<?php
/**
 * View helper: render halaman di dalam layout utama
 */
function view(string $file, array $data = []): void
{
    extract($data);
    $judulHalaman = $judul ?? 'Aplikasi Laundry';
    require __DIR__ . '/../views/layouts/layout.php';
}
