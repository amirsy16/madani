<?php

// Identitas lembaga untuk dokumen cetak (invoice, laporan PDF).
// Isi nilai yang belum terisi agar tampil lengkap pada dokumen resmi.
return [
    'lembaga' => [
        'nama' => 'LAZ INSAN MADANI JAMBI',
        'nama_tampil' => 'LAZ Insan Madani',
        'tagline' => 'Lembaga Amil Zakat',
        'legal' => 'SK Kementerian Agama RI No. 378 Tahun 2018',
        'alamat' => 'Jl. Otto Iskandardinata No. 15, Sei Asam, Pasar Jambi',
        'telp' => '0811.743.1231',
        'web' => 'insanmadanijambi.org',
        'email' => env('MAIL_FROM_ADDRESS'),
    ],
];
