<?php

namespace App\Filament\Imports;

use App\Models\Ptj;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make("name")
                ->requiredMapping()
                ->rules(["required", "max:255"]),

            ImportColumn::make("nokp")
                ->requiredMapping()
                ->rules([
                    "required",
                    "max:255",
                    Rule::unique('users', 'nokp'),
                ]),

            ImportColumn::make("email")
                ->requiredMapping()
                ->rules([
                    "required",
                    "email",
                    "max:255",
                    "ends_with:@moh.gov.my",
                    Rule::unique('users', 'email'),
                ])
            ,

            ImportColumn::make("ptj_id")
                ->requiredMapping()
                ->rules(["required"]),

            ImportColumn::make('password')
                ->requiredMapping()
                ->rules(['required', 'max:255']),

            ImportColumn::make("phone_number")
                ->requiredMapping()
                ->rules(["required", "max:255"]),

            ImportColumn::make("status")
                ->requiredMapping()
                ->rules(["required"]),

            ImportColumn::make("role")
                ->requiredMapping()
                ->rules(["required"]),
        ];
    }

    public function fillRecord(): void
    {
        $this->data['name'] = strtoupper(trim($this->data['name']));
        
        $ptj = Ptj::whereRaw('LOWER(nama_ptj) = ?', [
            strtolower(trim($this->data['ptj_id']))
        ])->first();

        if (!$ptj) {
            throw new \Exception("PTJ tidak dijumpai: {$this->data['ptj_id']}");
        }

        $this->data['ptj_id'] = $ptj->id;

        $this->data['status'] = match (strtolower(trim($this->data['status']))) {
            'aktif' => 1,
            'tidak aktif' => 0,
            default => throw new \Exception("Status tidak sah: {$this->data['status']}"),
        };

        $this->data['role'] = match (strtolower(trim($this->data['role']))) {
            'super admin', 'superadmin' => 1,
            'admin' => 2,
            'user' => 3,
            default => throw new \Exception("Role tidak sah: {$this->data['role']}"),
        };

        unset($this->data['ptj']);

        parent::fillRecord();
    }
    public function resolveRecord(): User
    {
        return User::firstOrNew([
            'nokp' => $this->data['nokp'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
