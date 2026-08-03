<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PesertaRequest;
use App\Imports\PesertaImport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class PesertaController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->peserta();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('username', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        $peserta = $query->latest()->paginate(15)->withQueryString();

        return view('superadmin.peserta.index', compact('peserta'));
    }

    public function create(): View
    {
        return view('superadmin.peserta.create');
    }

    public function store(PesertaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? $this->generateEmail($data['username']),
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'company_id' => null,
            'is_active' => $request->boolean('is_active', true),
            'is_peserta' => true,
        ]);

        return redirect()->route('superadmin.peserta.index')
            ->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function edit(User $peserta): View
    {
        abort_unless($peserta->isPeserta(), 404);

        return view('superadmin.peserta.edit', ['peserta' => $peserta]);
    }

    public function update(PesertaRequest $request, User $peserta): RedirectResponse
    {
        abort_unless($peserta->isPeserta(), 404);

        $data = $request->validated();

        $peserta->fill([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? $peserta->email ?? $this->generateEmail($data['username']),
            'phone' => $data['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if (! empty($data['password'])) {
            $peserta->password = Hash::make($data['password']);
        }

        $peserta->save();

        return redirect()->route('superadmin.peserta.index')
            ->with('success', 'Peserta berhasil diupdate.');
    }

    public function destroy(User $peserta): RedirectResponse
    {
        abort_unless($peserta->isPeserta(), 404);

        $peserta->delete();

        return redirect()->route('superadmin.peserta.index')
            ->with('success', 'Peserta berhasil dihapus.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $import = new PesertaImport;
        Excel::import($import, $request->file('file'));

        $message = "Import selesai: {$import->getSuccessCount()} peserta ditambahkan, {$import->getSkipCount()} dilewati.";

        return redirect()->route('superadmin.peserta.index')->with('success', $message);
    }

    private function generateEmail(string $username): string
    {
        return Str::lower($username).'-'.Str::random(6).'@peserta.local';
    }
}
