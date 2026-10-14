<div class="flex items-center gap-2 text-sm" role="status" aria-live="polite">
    <span x-show="state === 'saving'" x-cloak class="text-gray-500">Menyimpan...</span>
    <span x-show="state === 'saved'" x-cloak class="font-medium text-green-700">Urutan tersimpan</span>
    <span x-show="state === 'error'" x-cloak class="font-medium text-primary" x-text="message"></span>
</div>
