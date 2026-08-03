<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PesertaImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected int $successCount = 0;

    protected int $skipCount = 0;

    /** @var array<int, string> */
    protected array $errors = [];

    /**
     * @param  Collection<int, Collection<string, mixed>>  $rows
     */
    public function collection(Collection $rows): void
    {
        $currentRow = 1;

        foreach ($rows as $row) {
            $currentRow++;

            $name = trim((string) ($row['nama'] ?? $row['nama_lengkap'] ?? ''));
            $username = trim((string) ($row['username'] ?? ''));
            $password = trim((string) ($row['password'] ?? ''));

            if ($name === '' || $username === '') {
                $this->skipCount++;
                $this->errors[] = "Baris {$currentRow}: Nama atau Username kosong, dilewati.";

                continue;
            }

            if (User::where('username', $username)->exists()) {
                $this->skipCount++;
                $this->errors[] = "Baris {$currentRow}: Username '{$username}' sudah ada, dilewati.";

                continue;
            }

            $email = trim((string) ($row['email'] ?? ''));

            if ($email === '' || User::where('email', $email)->exists()) {
                $email = Str::lower($username).'-'.Str::random(6).'@peserta.local';
            }

            if ($password === '') {
                $password = Str::random(8);
            }

            User::create([
                'name' => $name,
                'username' => $username,
                'email' => $email,
                'phone' => trim((string) ($row['no_hp'] ?? $row['phone'] ?? '')) ?: null,
                'password' => Hash::make($password),
                'company_id' => null,
                'is_active' => true,
                'is_peserta' => true,
            ]);

            $this->successCount++;
        }
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getSkipCount(): int
    {
        return $this->skipCount;
    }

    /** @return array<int, string> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
