<div
    x-data="{
        theme: localStorage.getItem('theme') || 'dark',
        init() {
            if (this.theme === 'system' || ! ['dark', 'light'].includes(this.theme)) {
                this.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
                localStorage.setItem('theme', this.theme);
                $dispatch('theme-changed', this.theme);
            }
        },
        toggleTheme() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', this.theme);
            if (this.theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            $dispatch('theme-changed', this.theme);
        }
    }"
    class="fi-theme-switcher flex items-center justify-center p-1.5"
>
    <button
        type="button"
        x-on:click="toggleTheme()"
        title="تغییر پوسته (شب / روز)"
        aria-label="تغییر پوسته (شب / روز)"
        class="fi-theme-switcher-btn flex w-full items-center justify-center gap-2 rounded-lg bg-gray-100/80 hover:bg-gray-200/80 dark:bg-white/5 dark:hover:bg-white/10 py-2 px-3 transition duration-150 outline-none"
    >
        <img
            src="{{ asset('images/icons/app_moon_and_sun.svg') }}"
            alt="تغییر پوسته"
            class="h-6 w-6 object-contain"
        />
    </button>
</div>