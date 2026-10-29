<?php

use App\Models\BusinessActivity;
use App\Models\ServiceCategory;
use App\Support\PageContents;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penanda "subject" menunjuk item yang diubah ("category:12", "activity:5", "page:about"), dipakai
     * keterangan "Terakhir diubah oleh ..." di halaman ubah. Catatan lama diisi sebisanya lewat nama
     * pada keterangan; nama yang kembar (ambigu) dibiarkan kosong.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('subject', 60)->nullable()->after('menu')->index();
        });

        $this->backfillNamed('Kategori', 'kategori', 'category', ServiceCategory::query()->pluck('name', 'id')->all());
        $this->backfillNamed('Aktivitas Usaha', 'aktivitas', 'activity', BusinessActivity::query()->pluck('name', 'id')->all());
        $this->backfillPages();
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['subject']);
            $table->dropColumn('subject');
        });
    }

    /** @param  array<int, string>  $items  id => nama */
    private function backfillNamed(string $menu, string $word, string $prefix, array $items): void
    {
        $byName = [];
        foreach ($items as $id => $name) {
            $byName[$name][] = $id;
        }

        DB::table('activity_logs')
            ->where('menu', $menu)
            ->whereIn('action', ['created', 'updated'])
            ->whereNull('subject')
            ->orderBy('id')
            ->each(function ($row) use ($word, $prefix, $byName) {
                if (! preg_match('/^(?:Menambahkan|Mengubah) '.$word.' "(.*)"(?: \([^()]*\))?$/su', (string) $row->detail, $match)) {
                    return;
                }

                $ids = $byName[$match[1]] ?? [];

                if (count($ids) === 1) {
                    DB::table('activity_logs')->where('id', $row->id)->update(['subject' => "{$prefix}:{$ids[0]}"]);
                }
            });
    }

    private function backfillPages(): void
    {
        $keys = [];
        foreach (PageContents::definitions() as $key => $definition) {
            $keys[$definition['label']] = $key;
        }

        DB::table('activity_logs')
            ->where('menu', 'Konten Halaman')
            ->whereIn('action', ['updated', 'reset'])
            ->whereNull('subject')
            ->orderBy('id')
            ->each(function ($row) use ($keys) {
                $detail = (string) $row->detail;

                if (preg_match('/^Mengubah konten halaman (.+?) \(bagian yang diubah: /u', $detail, $match)
                    || preg_match('/^Mengembalikan seluruh konten halaman (.+?) ke setelan default /u', $detail, $match)) {
                    if (isset($keys[$match[1]])) {
                        DB::table('activity_logs')->where('id', $row->id)->update(['subject' => 'page:'.$keys[$match[1]]]);
                    }
                }
            });
    }
};
