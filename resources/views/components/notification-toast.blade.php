<div
    x-data="notifications"
    @notify.window="add($event.detail)"
    class="fixed top-4 right-4 z-50 space-y-2"
    aria-live="polite">
    <template x-for="(n, i) in items" :key="n.id">
        <div x-show="n.visible" x-transition.duration.300ms
            class="flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl shadow-black/10 text-sm font-medium max-w-sm backdrop-blur-sm border border-white/10"
            x-bind:class="{
                'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white': n.type === 'success',
                'bg-gradient-to-r from-red-500 to-rose-600 text-white': n.type === 'error',
                'bg-gradient-to-r from-amber-400 to-orange-500 text-white': n.type === 'warning',
                'bg-gradient-to-r from-blue-500 to-indigo-600 text-white': n.type === 'info',
            }">
            <template x-if="n.type === 'success'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </template>
            <template x-if="n.type === 'error'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </template>
            <template x-if="n.type === 'warning'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </template>
            <template x-if="n.type === 'info'">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </template>
            <span x-text="n.message" class="flex-1"></span>
            <button @click="remove(i)" class="text-white/60 hover:text-white transition-colors">&times;</button>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('notifications', () => ({
            items: [],
            nextId: 0,
            add(detail) {
                const id = this.nextId++;
                const item = {
                    id,
                    type: detail.type || 'info',
                    message: detail.message || '',
                    visible: true,
                };
                this.items.push(item);
                setTimeout(() => {
                    item.visible = false;
                    setTimeout(() => this.items = this.items.filter(i => i.id !== id), 300);
                }, detail.duration || 4000);
            },
            remove(index) {
                const item = this.items[index];
                if (item) { item.visible = false; setTimeout(() => this.items.splice(index, 1), 300); }
            }
        }));

        window.notify = (type, message, duration) => {
            window.dispatchEvent(new CustomEvent('notify', { detail: { type, message, duration } }));
        };
    });
</script>
