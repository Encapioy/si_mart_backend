<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Baris Bahasa untuk Validasi
    |--------------------------------------------------------------------------
    */

    'accepted'        => ':attribute harus diterima.',
    'active_url'      => ':attribute bukan URL yang valid.',
    'after'           => ':attribute harus tanggal setelah :date.',
    'alpha'           => ':attribute hanya boleh berisi huruf.',
    'alpha_dash'      => ':attribute hanya boleh berisi huruf, angka, strip, dan garis bawah.',
    'alpha_num'       => ':attribute hanya boleh berisi huruf dan angka.',
    'array'           => ':attribute harus berupa array.',
    'before'          => ':attribute harus tanggal sebelum :date.',
    'between'         => [
        'numeric' => ':attribute harus antara :min dan :max.',
        'file'    => ':attribute harus antara :min dan :max kilobytes.',
        'string'  => ':attribute harus antara :min dan :max karakter.',
        'array'   => ':attribute harus antara :min dan :max item.',
    ],
    'boolean'         => ':attribute harus berupa true atau false.',
    'confirmed'       => 'Konfirmasi :attribute tidak cocok.',
    'date'            => ':attribute bukan tanggal yang valid.',
    'date_format'     => ':attribute tidak cocok dengan format :format.',
    'different'       => ':attribute dan :other harus berbeda.',
    'digits'          => ':attribute harus berupa angka :digits digit.',
    'digits_between'  => ':attribute harus antara angka :min dan :max.',
    'dimensions'      => ':attribute tidak memiliki dimensi gambar yang valid.',
    'distinct'        => ':attribute memiliki nilai yang duplikat.',
    'email'           => ':attribute harus berupa alamat surel yang valid.',
    'exists'          => ':attribute yang dipilih tidak valid.',
    'file'            => ':attribute harus berupa sebuah berkas.',
    'filled'          => ':attribute harus memiliki nilai.',
    'image'           => ':attribute harus berupa gambar.',
    'in'              => ':attribute yang dipilih tidak valid.',
    'in_array'        => ':attribute tidak ada di dalam :other.',
    'integer'         => ':attribute harus merupakan bilangan bulat.',
    'ip'              => ':attribute harus berupa alamat IP yang valid.',
    'json'            => ':attribute harus berupa JSON string yang valid.',
    'max'             => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'file'    => ':attribute tidak boleh lebih dari :max kilobytes.',
        'string'  => ':attribute tidak boleh lebih dari :max karakter.',
        'array'   => ':attribute tidak boleh lebih dari :max item.',
    ],
    'mimes'           => ':attribute harus berupa berkas berjenis: :values.',
    'mimetypes'       => ':attribute harus berupa berkas berjenis: :values.',
    'min'             => [
        'numeric' => ':attribute minimal harus :min.',
        'file'    => ':attribute minimal harus :min kilobytes.',
        'string'  => ':attribute minimal harus :min karakter.',
        'array'   => ':attribute minimal harus :min item.',
    ],
    'not_in'          => ':attribute yang dipilih tidak valid.',
    'numeric'         => ':attribute harus berupa angka.',
    'present'         => ':attribute wajib ada.',
    'regex'           => 'Format :attribute tidak valid.',
    'required'        => ':attribute wajib diisi.', // <--- INI YANG PALING SERING MUNCUL
    'required_if'     => ':attribute wajib diisi bila :other adalah :value.',
    'required_unless' => ':attribute wajib diisi kecuali :other memiliki nilai :values.',
    'required_with'   => ':attribute wajib diisi bila terdapat :values.',
    'required_with_all' => ':attribute wajib diisi bila terdapat :values.',
    'required_without' => ':attribute wajib diisi bila tidak terdapat :values.',
    'required_without_all' => ':attribute wajib diisi bila tidak terdapat ada :values.',
    'same'            => ':attribute dan :other harus sama.',
    'size'            => [
        'numeric' => ':attribute harus berukuran :size.',
        'file'    => ':attribute harus berukuran :size kilobyte.',
        'string'  => ':attribute harus berukuran :size karakter.',
        'array'   => ':attribute harus mengandung :size item.',
    ],
    'string'          => ':attribute harus berupa string.',
    'timezone'        => ':attribute harus berupa zona waktu yang valid.',
    'unique'          => ':attribute sudah ada yang menggunakan.',
    'uploaded'        => ':attribute gagal diunggah.',
    'url' => 'Format :attribute tidak valid.',


    /*
    |--------------------------------------------------------------------------
    | Kustomisasi Nama Atribut (Agar lebih enak dibaca)
    |--------------------------------------------------------------------------
    |
    | Di sini kamu mengubah nama field database (misal: 'no_hp') menjadi
    | bahasa manusia (misal: 'Nomor Handphone').
    |
    */

    'attributes' => [
        'username' => 'Username',
        'password' => 'Kata Sandi',
        'email'    => 'Alamat Email',
        'amount'   => 'Nominal',
        'pin'      => 'PIN Keamanan',
        'no_hp'    => 'Nomor HP',
        'name'     => 'Nama Lengkap',
        'saldo'    => 'Saldo',
    ],

];