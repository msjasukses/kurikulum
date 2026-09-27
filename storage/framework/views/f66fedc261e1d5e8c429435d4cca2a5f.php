<script>
    // Pilihan Jam Mengajar (checkbox) mengikuti Hari dan Kelas yang dipilih.
    // Jam yang di master Jam Mengajar kolom Hari/Tingkat Kelas-nya dikosongkan
    // berarti berlaku umum, jadi selalu ikut muncul.
    (function () {
        var JAM = <?php echo json_encode($jamMengajarList, 15, 512) ?>;
        var TINGKAT_KELAS = <?php echo json_encode($tingkatPerKelas, 15, 512) ?>;

        var $hari = $('[name="hari"]');
        var $kelas = $('[name="kelas_id"]');
        var $wadah = $('#pilihan-jam_mengajar_id');

        if (! $wadah.length) {
            return;
        }

        // Jam yang sudah tersimpan pada jadwal yang sedang diubah: tetap
        // ditampilkan walau di luar filter supaya tidak ikut terhapus.
        var jamTersimpan = String($wadah.data('awal') || '');

        var $pesan = $('<div class="text-muted small">Belum ada jam mengajar untuk hari/kelas ini.</div>').hide();
        $wadah.append($pesan);

        function cocok(jam, hari, tingkat) {
            var hariCocok = ! hari || ! jam.hari || jam.hari === hari;
            var tingkatCocok = ! tingkat || ! jam.tingkat_kelas_id || String(jam.tingkat_kelas_id) === String(tingkat);

            return hariCocok && tingkatCocok;
        }

        function saringJam() {
            var hari = $hari.val();
            var tingkat = TINGKAT_KELAS[$kelas.val()] || null;
            var tampil = 0;

            $wadah.find('.form-check').each(function () {
                var $baris = $(this);
                var $centang = $baris.find('input[type="checkbox"]');
                var nilai = String($centang.val());
                var jam = JAM.find(function (j) { return String(j.id) === nilai; });
                var boleh = jam ? cocok(jam, hari, tingkat) : true;

                if (! boleh && nilai === jamTersimpan) {
                    boleh = true; // jam yang sudah tersimpan tetap tampil
                }

                $baris.toggleClass('d-none', ! boleh);

                if (boleh) {
                    tampil++;
                } else {
                    $centang.prop('checked', false);
                }
            });

            $pesan.toggle(tampil === 0);
        }

        $hari.on('change', saringJam);
        $kelas.on('change', saringJam);
        saringJam();
    })();
</script>
<?php /**PATH C:\laragon\www\kurikulum\resources\views/kurikulum/jadwal-form-script.blade.php ENDPATH**/ ?>