<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AduanSaran extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';
    protected static ?string $navigationLabel = 'Aduan & Saran';
    protected static ?string $title = 'Aduan & Saran';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.aduan-saran';

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $this->form->fill([
            'nama_pengirim' => $user?->name ?? '',
            'email_pengirim' => $user?->email ?? '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pengirim')
                    ->description('Data ini diambil otomatis dari akun Anda.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_pengirim')
                            ->label('Nama')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('email_pengirim')
                            ->label('Email')
                            ->disabled()
                            ->dehydrated(),
                    ]),

                Section::make('Detail Laporan')
                    ->columns(2)
                    ->schema([
                        Select::make('jenis')
                            ->label('Jenis Laporan')
                            ->options([
                                'Aduan'       => 'Aduan (ada masalah / bug)',
                                'Saran'       => 'Saran (ide / pengembangan)',
                                'Pertanyaan'  => 'Pertanyaan',
                                'Lainnya'     => 'Lainnya',
                            ])
                            ->required()
                            ->live(),

                        Select::make('prioritas')
                            ->label('Prioritas')
                            ->options([
                                'Rendah'  => 'Rendah',
                                'Sedang'  => 'Sedang',
                                'Tinggi'  => 'Tinggi',
                            ])
                            ->default('Sedang')
                            ->visible(fn ($get) => $get('jenis') === 'Aduan')
                            ->required(fn ($get) => $get('jenis') === 'Aduan'),

                        TextInput::make('judul')
                            ->label('Judul')
                            ->placeholder('Singkat dan jelas...')
                            ->required()
                            ->maxLength(150)
                            ->columnSpanFull(),

                        Textarea::make('pesan')
                            ->label('Isi Pesan')
                            ->placeholder('Jelaskan secara detail...')
                            ->required()
                            ->rows(6)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $prioritasLabel = isset($data['prioritas']) ? " | Prioritas: {$data['prioritas']}" : '';

        $subject = "[{$data['jenis']}{$prioritasLabel}] {$data['judul']}";
        $message = "Jenis    : {$data['jenis']}\n";
        if (!empty($data['prioritas'])) {
            $message .= "Prioritas: {$data['prioritas']}\n";
        }
        $message .= "Dari     : {$data['nama_pengirim']} ({$data['email_pengirim']})\n";
        $message .= "Judul    : {$data['judul']}\n\n";
        $message .= "Pesan:\n{$data['pesan']}";

        // Dispatch ke browser agar Alpine.js yang submit langsung ke Web3Forms
        $this->dispatch('send-to-web3forms', payload: [
            'access_key' => config('services.web3forms.access_key'),
            'subject'    => $subject,
            'from_name'  => $data['nama_pengirim'] . ' — Sistem Madani',
            'email'      => $data['email_pengirim'],
            'replyto'    => $data['email_pengirim'],
            'message'    => $message,
            'botcheck'   => '',
        ]);
    }

    public function notifySuccess(): void
    {
        Notification::make()
            ->title('Laporan Terkirim!')
            ->body('Terima kasih. Laporan Anda telah dikirimkan ke administrator.')
            ->success()
            ->duration(6000)
            ->send();

        $this->form->fill([
            'nama_pengirim'  => Auth::user()?->name ?? '',
            'email_pengirim' => Auth::user()?->email ?? '',
            'jenis'          => null,
            'prioritas'      => 'Sedang',
            'judul'          => '',
            'pesan'          => '',
        ]);
    }

    public function notifyError(string $message = ''): void
    {
        Notification::make()
            ->title('Gagal Mengirim')
            ->body($message ?: 'Terjadi kesalahan. Silakan coba lagi.')
            ->danger()
            ->duration(8000)
            ->send();
    }
}
