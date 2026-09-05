<?php
$judul_halaman    = isset($judul_halaman) ? $judul_halaman : 'Kalkulator Area Pencarian SAR';
$subjudul_halaman = isset($subjudul_halaman) ? $subjudul_halaman : 'Kantor Pencarian dan Pertolongan Aceh';
?>
<div class="hero">
    <div class="hero-inner">
        <div class="emblem" aria-hidden="true">
            <img src="logo-basarnas.png" alt="" width="69" height="86">
        </div>
        <div class="hero-text">
            <h1 class="hero-title"><?php echo htmlspecialchars($judul_halaman); ?></h1>
            <p class="hero-subtitle"><?php echo htmlspecialchars($subjudul_halaman); ?></p>
        </div>
    </div>
</div>