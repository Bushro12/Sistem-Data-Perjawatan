<?php

namespace App\Filament\Resources\Pegawais\Schemas;

use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PegawaiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Maklumat Pegawai')
                            ->schema([
                                TextEntry::make('nama')
                                    ->columnSpanFull(),
                                TextEntry::make('nokp')
                                    ->label('No Kad Pengenalan'),
                                TextEntry::make('jantina'),
                                TextEntry::make('jawatan_gred.jawatan.desc_jawatan')
                                    ->label('Jawatan'),
                                TextEntry::make('jawatan_gred.gred.kod_gred')
                                    ->label('Gred')
                            ]),

                        Tab::make('Penempatan')
                            ->schema([
                                TextEntry::make('ptj.nama_ptj')
                                    ->label('PTJ')
                                    ->columnSpanFull(),
                                TextEntry::make('bahagian.nama_bahagian')
                                    ->label('Bahagian')
                                    ->columnSpanFull(),
                                TextEntry::make('unit')
                                    ->label('Unit')
                                    ->getStateUsing(function ($record) {
                                        if ($record->unit_id !== null) {
                                            return $record->unit?->nama_unit;
                                        }

                                        if ($record->ada_unit == 1) {
                                            return 'TIADA';
                                        }

                                        return null;
                                    }),
                                TextEntry::make('subunit')
                                    ->label('Sub Unit')
                                    ->getStateUsing(function ($record) {
                                        if ($record->subunit_id !== null) {
                                            return $record->subunit?->nama_subunit;
                                        }

                                        if ($record->ada_subunit == 1) {
                                            return 'TIADA';
                                        }

                                        return null;
                                    })
                            ]),

                        Tab::make('Jenis Lantikan')
                            ->schema([
                                TextEntry::make('lantikan')
                                    ->getStateUsing(function ($record) {
                                        if ($record->is_tetap == 1) {
                                            return 'Tetap';
                                        }

                                        if ($record->is_kontrak == 1) {
                                            return 'Kontrak';
                                        }

                                        if ($record->is_kontrak_interim == 1) {
                                            return 'Kontrak Interim';
                                        }
                                    }),

                                TextEntry::make('lain-lain')
                                    ->label('Lain-lain')
                                    ->getStateUsing(function ($record) {
                                        if ($record->is_kup == 1) {
                                            return 'Khas Untuk Penyandang (KUP)';
                                        }

                                        if ($record->is_kupj == 1) {
                                            return 'Khas Untuk Penyandang Jawatan (KUPJ)';
                                        }

                                        if ($record->is_jtw == 1) {
                                            return 'Jawatan Tanpa Waran (JTW)';
                                        }

                                        return 'Tiada';
                                    }),

                                TextEntry::make('tarikh_lantikan')
                                    ->label('Tarikh Lantikan')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->tarikh_lantikan)->format('d-m-Y');
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_tetap == 1
                                            || $record->is_kontrak_interim == 1;
                                    }),
                                TextEntry::make('tarikh_sah_jawatan')
                                    ->label('Tarikh Sah Jawatan')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->tarikh_sah_jawatan)->format('d-m-Y');
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_tetap == 1
                                            || $record->is_kontrak_interim == 1;
                                    }),
                                TextEntry::make('opsyenPencen.opsyen')
                                    ->label('Opsyen Pencen')
                                    ->visible(function ($record) {
                                        return $record->is_tetap == 1
                                            || $record->is_kontrak_interim == 1;
                                    }),
                                TextEntry::make('tarikh_pencen')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->tarikh_pencen)->format('d-m-Y');
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_tetap == 1
                                            || $record->is_kontrak_interim == 1;
                                    }),

                                TextEntry::make('tarikh_lantikan1')
                                    ->label('Tarikh Lantikan 1')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_lantikan1)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_lantikan1;
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_lantikan1 !== null;
                                    }),

                                TextEntry::make('tarikh_tamat1')
                                    ->label('Tarikh Tamat 1')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_tamat1)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_tamat1;
                                    })->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_tamat1 !== null;
                                    }),

                                TextEntry::make('tarikh_lantikan2')
                                    ->label('Tarikh Lantikan 2')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_lantikan2)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_lantikan2;
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_lantikan2 !== null;
                                    }),

                                TextEntry::make('tarikh_tamat2')
                                    ->label('Tarikh Tamat 2')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_tamat2)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_tamat2;
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_tamat2 !== null;
                                    }),

                                TextEntry::make('tarikh_lantikan3')
                                    ->label('Tarikh Lantikan 3')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_lantikan3)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_lantikan3;
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_lantikan3 !== null;
                                    }),

                                TextEntry::make('tarikh_tamat3')
                                    ->label('Tarikh Tamat 3')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_tamat3)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_tamat3;
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_tamat3 !== null;
                                    }),

                                TextEntry::make('tarikh_lantikan4')
                                    ->label('Tarikh Lantikan 4')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_lantikan4)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_lantikan4;
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_lantikan4 !== null;
                                    }),

                                TextEntry::make('tarikh_tamat4')
                                    ->label('Tarikh Tamat 4')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_tamat4)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_tamat4;
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_tamat4 !== null;
                                    }),

                                TextEntry::make('tarikh_lantikan5')
                                    ->label('Tarikh Lantikan 5')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_lantikan5)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_lantikan5;
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_lantikan5 !== null;
                                    }),

                                TextEntry::make('tarikh_tamat5')
                                    ->label('Tarikh Tamat 5')
                                    ->formatStateUsing(function ($record) {
                                        return Carbon::parse($record->pegawaiKontrak?->tarikh_tamat5)->format('d-m-Y');
                                    })
                                    ->getStateUsing(function ($record) {
                                        return $record->pegawaiKontrak?->tarikh_tamat5;
                                    })
                                    ->visible(function ($record) {
                                        return $record->is_kontrak == 1
                                            && $record->pegawaiKontrak?->tarikh_tamat5 !== null;
                                    }),
                            ])
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }
}
