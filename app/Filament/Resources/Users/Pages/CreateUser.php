<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

     protected ?string $plainPassword = null;

    public function getTitle(): string
    {
        return 'Tambah Pengguna';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Tambah');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }

     protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->plainPassword = $data['password'];

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        Mail::send('emails.user-created', [
            'user' => $user,
            'password' => $this->plainPassword,
        ], function ($message) use ($user) {
            $message
                ->to($user->email)
                ->subject('Daftar Pengguna Sistem MySTAFF');
        });

        $creator = auth()->user();

        $recipients = User::whereIn('role', [1, 2])->get();

        Notification::make()
            ->title('User Created')
            ->body("New user was created by {$creator->name}")
            ->sendToDatabase($recipients);
    }
}
