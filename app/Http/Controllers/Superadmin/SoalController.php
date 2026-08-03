<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SoalRequest;
use App\Models\JenisUjian;
use App\Models\Soal;
use App\Models\SubIndikator;
use App\Models\SubJenisUjian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SoalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Soal::with('subIndikator.subJenisUjian.jenisUjian', 'pembuat');

        if ($request->filled('sub_indikator_id')) {
            $query->where('sub_indikator_id', $request->sub_indikator_id);
        } elseif ($request->filled('sub_jenis_ujian_id')) {
            $query->whereHas('subIndikator', function ($q) use ($request) {
                $q->where('sub_jenis_ujian_id', $request->sub_jenis_ujian_id);
            });
        } elseif ($request->filled('jenis_ujian_id')) {
            $query->whereHas('subIndikator.subJenisUjian', function ($q) use ($request) {
                $q->where('jenis_ujian_id', $request->jenis_ujian_id);
            });
        }

        if ($request->filled('search')) {
            $query->where('soal', 'like', '%'.$request->search.'%');
        }

        $soals = $query->latest()->paginate(15)->withQueryString();
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->get();

        return view('superadmin.soal.index', compact('soals', 'jenisUjians'));
    }

    public function create(Request $request): View
    {
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->get();
        $subIndikator = null;

        if ($request->filled('sub_indikator_id')) {
            $subIndikator = SubIndikator::with('subJenisUjian')->find($request->sub_indikator_id);
        }

        return view('superadmin.soal.create', compact('jenisUjians', 'subIndikator'));
    }

    public function store(SoalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['pembuat_soal_id'] = $request->user()->id;

        foreach ($this->imageFields() as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('panritta/soal', 'public');
            }
        }

        Soal::create($data);

        return redirect()->route('superadmin.soal.index')
            ->with('success', 'Soal berhasil dibuat.');
    }

    public function edit(Soal $soal): View
    {
        $soal->load('subIndikator.subJenisUjian.jenisUjian');
        $jenisUjians = JenisUjian::orderBy('nama_jenis_ujian')->get();

        return view('superadmin.soal.edit', compact('soal', 'jenisUjians'));
    }

    public function update(SoalRequest $request, Soal $soal): RedirectResponse
    {
        $data = $request->validated();

        foreach ($this->imageFields() as $field) {
            if ($request->hasFile($field)) {
                if ($soal->{$field}) {
                    Storage::disk('public')->delete($soal->{$field});
                }
                $data[$field] = $request->file($field)->store('panritta/soal', 'public');
            }
        }

        $subJenis = $request->resolveSubJenisUjian();

        if ($subJenis && $subJenis->sistem_penilaian === 'benar_salah') {
            $data['nilai_bobot_a'] = null;
            $data['nilai_bobot_b'] = null;
            $data['nilai_bobot_c'] = null;
            $data['nilai_bobot_d'] = null;
            $data['nilai_bobot_e'] = null;
        } else {
            $data['kunci_jawaban'] = null;
            $data['nilai_bobot_benar'] = null;
        }

        $soal->update($data);

        return redirect()->route('superadmin.soal.index')
            ->with('success', 'Soal berhasil diupdate.');
    }

    public function destroy(Soal $soal): RedirectResponse
    {
        foreach ($this->imageFields() as $field) {
            if ($soal->{$field}) {
                Storage::disk('public')->delete($soal->{$field});
            }
        }

        $soal->delete();

        return redirect()->route('superadmin.soal.index')
            ->with('success', 'Soal berhasil dihapus.');
    }

    public function subJenisUjianOptions(JenisUjian $jenisUjian): JsonResponse
    {
        $options = SubJenisUjian::where('jenis_ujian_id', $jenisUjian->id)
            ->orderBy('nama_sub_jenis_ujian')
            ->get(['id', 'nama_sub_jenis_ujian', 'sistem_penilaian', 'jumlah_jawaban_pilihan_ganda', 'nilai_benar']);

        return response()->json($options);
    }

    public function subIndikatorOptions(SubJenisUjian $subJenisUjian): JsonResponse
    {
        $options = SubIndikator::where('sub_jenis_ujian_id', $subJenisUjian->id)
            ->orderBy('nama_sub_indikator')
            ->get(['id', 'nama_sub_indikator']);

        return response()->json($options);
    }

    /**
     * @return array<int, string>
     */
    private function imageFields(): array
    {
        return [
            'gambar_soal',
            'gambar_opsi_a',
            'gambar_opsi_b',
            'gambar_opsi_c',
            'gambar_opsi_d',
            'gambar_opsi_e',
            'gambar_pembahasan',
        ];
    }
}
