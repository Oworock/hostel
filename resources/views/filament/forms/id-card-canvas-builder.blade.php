<section
    class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 space-y-4"
    x-data="simpleIdCardBuilder($wire)"
    x-init="init()"
>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold">{{ __('ID Card Layout Builder') }}</h3>
            <p class="text-sm text-gray-500">{{ __('Edit blocks on the left and preview the card on the right. Changes save automatically.') }}</p>
        </div>
        <button
            type="button"
            @click="resetDefaults()"
            class="fi-btn fi-btn-size-sm rounded-lg px-3 py-2 text-sm font-semibold border border-slate-300 dark:border-slate-700"
        >
            {{ __('Reset Defaults') }}
        </button>
    </div>

    <div class="grid gap-4 lg:grid-cols-[430px_1fr]">
        <aside class="rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 space-y-3">
            <div class="flex gap-2">
                <select
                    x-model="newField"
                    class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-sm"
                >
                    <template x-for="field in availableFields" :key="field.value">
                        <option :value="field.value" x-text="field.label"></option>
                    </template>
                </select>
                <button
                    type="button"
                    @click="addBlock()"
                    class="fi-btn fi-btn-size-sm fi-color-primary rounded-lg px-3 py-2 text-sm font-semibold"
                >
                    {{ __('Add') }}
                </button>
            </div>

            <div class="max-h-[520px] overflow-auto pr-1 space-y-2">
                <template x-for="(item, index) in items" :key="item._id">
                    <div
                        class="rounded-lg border p-3 space-y-2"
                        :class="selectedIndex === index
                            ? 'border-blue-400 bg-blue-50/40 dark:border-blue-700 dark:bg-blue-900/20'
                            : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/40'"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <button
                                type="button"
                                @click="selectedIndex = index"
                                class="text-sm font-medium text-left truncate flex-1"
                                x-text="previewText(item.key)"
                            ></button>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="moveUp(index)" class="text-xs px-2 py-1 rounded bg-slate-100 dark:bg-slate-700">↑</button>
                                <button type="button" @click="moveDown(index)" class="text-xs px-2 py-1 rounded bg-slate-100 dark:bg-slate-700">↓</button>
                                <button type="button" @click="duplicate(index)" class="text-xs px-2 py-1 rounded bg-slate-100 dark:bg-slate-700">{{ __('Copy') }}</button>
                                <button type="button" @click="remove(index)" class="text-xs px-2 py-1 rounded bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-300">{{ __('Del') }}</button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] text-slate-500 mb-1">{{ __('Field') }}</label>
                            <select
                                x-model="item.key"
                                @change="normalizeBlock(index); sync()"
                                class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-sm"
                            >
                                <template x-for="field in availableFields" :key="field.value">
                                    <option :value="field.value" x-text="field.label"></option>
                                </template>
                            </select>
                        </div>

                        <template x-if="String(item.key || '').startsWith('text:')">
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">{{ __('Custom Text') }}</label>
                                <input
                                    type="text"
                                    :value="String(item.key || '').slice(5)"
                                    @input="item.key = `text:${$event.target.value}`; sync()"
                                    class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-sm"
                                />
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">X</label>
                                <input type="number" x-model.number="item.x" @input="sync()" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-sm" />
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-1">Y</label>
                                <input type="number" x-model.number="item.y" @input="sync()" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-sm" />
                            </div>
                        </div>

                        <template x-if="!isImageBlock(item)">
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">{{ __('Size') }}</label>
                                    <input type="number" min="10" max="72" x-model.number="item.size" @input="sync()" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">{{ __('Weight') }}</label>
                                    <select x-model.number="item.weight" @change="sync()" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-sm">
                                        <option value="400">400</option>
                                        <option value="500">500</option>
                                        <option value="700">700</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">{{ __('Color') }}</label>
                                    <input type="color" x-model="item.color" @input="sync()" class="block h-10 w-full rounded-lg border border-gray-300 dark:border-gray-700" />
                                </div>
                            </div>
                        </template>

                        <template x-if="isImageBlock(item)">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">{{ __('Width') }}</label>
                                    <input type="number" min="24" x-model.number="item.width" @input="sync()" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-sm" />
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">{{ __('Height') }}</label>
                                    <input type="number" min="24" x-model.number="item.height" @input="sync()" class="fi-input block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-950 text-sm" />
                                </div>
                            </div>
                        </template>

                        <label class="inline-flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                            <input type="checkbox" x-model="item.locked" @change="sync()" class="rounded border-gray-300 dark:border-gray-700" />
                            {{ __('Lock block') }}
                        </label>
                    </div>
                </template>
            </div>
        </aside>

        <div class="overflow-auto rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-950 p-3">
            <svg
                viewBox="0 0 760 420"
                class="block w-full min-w-[760px] max-w-[980px] rounded-lg shadow bg-slate-900"
                xmlns="http://www.w3.org/2000/svg"
            >
                <defs>
                    <linearGradient id="simple-id-card-bg" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#0f172a" />
                        <stop offset="100%" stop-color="#1e3a8a" />
                    </linearGradient>
                </defs>

                <rect x="0" y="0" width="760" height="420" fill="#e2e8f0"></rect>
                <template x-if="backgroundUrl">
                    <image :href="backgroundUrl" x="0" y="0" width="760" height="420" preserveAspectRatio="none"></image>
                </template>
                <template x-if="!backgroundUrl">
                    <rect x="25" y="25" width="710" height="370" rx="18" fill="url(#simple-id-card-bg)"></rect>
                </template>

                <template x-for="(item, index) in items" :key="item._id">
                    <g @click="selectedIndex = index" style="cursor:pointer">
                        <template x-if="isImageBlock(item)">
                            <image :href="itemPreview(item)" :x="item.x" :y="item.y" :width="item.width" :height="item.height" preserveAspectRatio="xMidYMid meet"></image>
                        </template>
                        <template x-if="!isImageBlock(item)">
                            <text
                                :x="item.x"
                                :y="item.y"
                                :font-size="item.size || 14"
                                :fill="item.color || '#ffffff'"
                                :font-weight="item.weight || 400"
                                font-family="Arial, Helvetica, sans-serif"
                                text-rendering="geometricPrecision"
                                x-text="previewText(item.key)"
                            ></text>
                        </template>
                        <rect
                            x-show="selectedIndex === index"
                            :x="item.x - 6"
                            :y="item.y - (isImageBlock(item) ? 6 : 18)"
                            :width="(isImageBlock(item) ? item.width : Math.max(120, previewText(item.key).length * 7)) + 12"
                            :height="(isImageBlock(item) ? item.height : (item.size || 14) + 18) + 12"
                            fill="none"
                            stroke="#60a5fa"
                            stroke-width="1.4"
                            rx="5"
                        ></rect>
                    </g>
                </template>
            </svg>
        </div>
    </div>
</section>

@once
    @push('scripts')
<script>
    window.simpleIdCardBuilder = function ($wire) {
        return {
            items: [],
            selectedIndex: null,
            newField: 'full_name',
            backgroundUrl: '',
            availableFields: [
                { value: '__logo', label: 'Logo Block' },
                { value: '__photo', label: 'Photo Block' },
                { value: '__app_name', label: 'System Name' },
                { value: '__card_title', label: 'Card Title' },
                { value: 'full_name', label: 'Full Name' },
                { value: 'job_title', label: 'Role / Job Title' },
                { value: '__label_staff_code', label: 'Label: Staff Code' },
                { value: '__label_department', label: 'Label: Department' },
                { value: '__label_email', label: 'Label: Email' },
                { value: '__label_phone', label: 'Label: Phone' },
                { value: '__label_joined', label: 'Label: Joined' },
                { value: 'employee_code', label: 'Staff Code Value' },
                { value: 'department', label: 'Department Value' },
                { value: 'email', label: 'Email Value' },
                { value: 'phone', label: 'Phone Value' },
                { value: 'joined_on', label: 'Joined Value' },
                { value: 'category', label: 'Category' },
                { value: 'hostel', label: 'Hostel' },
                { value: 'text:Custom Text', label: 'Custom Text' },
            ],
            init() {
                this.loadItems();
                this.refreshBackground();
                setInterval(() => this.refreshBackground(), 1200);
            },
            uid() {
                return Math.random().toString(36).slice(2, 11);
            },
            defaults() {
                return [
                    { key: '__logo', x: 545, y: 32, width: 170, height: 34, locked: false },
                    { key: '__photo', x: 545, y: 75, width: 130, height: 160, locked: false },
                    { key: '__app_name', x: 55, y: 72, size: 24, color: '#e2e8f0', weight: 700, locked: false },
                    { key: '__card_title', x: 55, y: 105, size: 16, color: '#93c5fd', weight: 600, locked: false },
                    { key: 'full_name', x: 55, y: 162, size: 30, color: '#ffffff', weight: 700, locked: false },
                    { key: 'job_title', x: 55, y: 198, size: 16, color: '#cbd5e1', weight: 500, locked: false },
                    { key: '__label_staff_code', x: 55, y: 252, size: 14, color: '#93c5fd', weight: 600, locked: false },
                    { key: '__label_department', x: 55, y: 280, size: 14, color: '#93c5fd', weight: 600, locked: false },
                    { key: '__label_email', x: 55, y: 308, size: 14, color: '#93c5fd', weight: 600, locked: false },
                    { key: '__label_phone', x: 55, y: 336, size: 14, color: '#93c5fd', weight: 600, locked: false },
                    { key: '__label_joined', x: 55, y: 364, size: 14, color: '#93c5fd', weight: 600, locked: false },
                    { key: 'employee_code', x: 185, y: 252, size: 14, color: '#ffffff', weight: 600, locked: false },
                    { key: 'department', x: 185, y: 280, size: 14, color: '#ffffff', weight: 600, locked: false },
                    { key: 'email', x: 185, y: 308, size: 14, color: '#ffffff', weight: 600, locked: false },
                    { key: 'phone', x: 185, y: 336, size: 14, color: '#ffffff', weight: 600, locked: false },
                    { key: 'joined_on', x: 185, y: 364, size: 14, color: '#ffffff', weight: 600, locked: false },
                ];
            },
            normalizePath(path) {
                return String(path || '').replace(/^\/+/, '').replace(/^(storage\/|public\/)/, '');
            },
            normalizeBlock(index) {
                if (!this.items[index]) return;
                this.items[index] = this.normalizeItem(this.items[index]);
            },
            normalizeItem(row) {
                const key = String(row?.key || 'full_name');
                const imageBlock = this.isImageKey(key);
                return {
                    _id: row?._id || this.uid(),
                    key,
                    x: Number(row?.x ?? (imageBlock ? 545 : 55)),
                    y: Number(row?.y ?? (imageBlock ? 75 : 160)),
                    size: Number(row?.size ?? 14),
                    color: String(row?.color || '#ffffff'),
                    weight: Number(row?.weight ?? 400),
                    width: Number(row?.width ?? (key === '__logo' ? 170 : 130)),
                    height: Number(row?.height ?? (key === '__logo' ? 34 : 160)),
                    locked: Boolean(row?.locked ?? false),
                };
            },
            parseRowsFromWire(current) {
                if (Array.isArray(current)) return current;
                if (typeof current === 'string') {
                    try {
                        const decoded = JSON.parse(current);
                        return Array.isArray(decoded) ? decoded : [];
                    } catch (e) {
                        return [];
                    }
                }
                if (current && typeof current === 'object') {
                    if (Array.isArray(current.value)) return current.value;
                    return Object.values(current).filter((row) => row && typeof row === 'object');
                }
                return [];
            },
            ensureDefaults(rows) {
                const byKey = new Map(rows.map((row) => [String(row.key), true]));
                for (const def of this.defaults()) {
                    if (!byKey.has(def.key)) rows.push(this.normalizeItem(def));
                }
                return rows;
            },
            loadItems() {
                const current = $wire.get('data.staff_payroll_id_card_layout_json');
                let rows = this.parseRowsFromWire(current).map((row) => this.normalizeItem(row));
                if (!rows.length) rows = this.defaults().map((row) => this.normalizeItem(row));
                this.items = this.ensureDefaults(rows);
                if (this.selectedIndex === null && this.items.length) this.selectedIndex = 0;
                this.sync();
            },
            refreshBackground() {
                let value = $wire.get('data.staff_payroll_id_card_background_template');
                if (Array.isArray(value)) value = value[0] || '';
                if (typeof value === 'object' && value !== null) value = '';
                value = String(value || '').trim();
                this.backgroundUrl = value ? `/storage/${this.normalizePath(value)}` : '';
            },
            isImageKey(key) {
                return key === '__logo' || key === '__photo';
            },
            isImageBlock(item) {
                return this.isImageKey(String(item?.key || ''));
            },
            logoPreview() {
                const useCustom = Boolean($wire.get('data.staff_payroll_id_card_use_custom_brand'));
                let logo = '';
                if (useCustom) logo = $wire.get('data.staff_payroll_id_card_brand_logo') || '';
                if (!logo) logo = '/favicon.svg';
                if (Array.isArray(logo)) logo = logo[0] || '';
                if (typeof logo === 'object' && logo !== null) logo = '';
                logo = String(logo || '').trim();
                if (logo.startsWith('http://') || logo.startsWith('https://') || logo.startsWith('data:') || logo.startsWith('/')) return logo;
                return logo ? `/storage/${this.normalizePath(logo)}` : '/favicon.svg';
            },
            photoPreview() {
                return 'data:image/svg+xml;utf8,' + encodeURIComponent('<svg xmlns="http://www.w3.org/2000/svg" width="140" height="170"><rect width="100%" height="100%" fill="#cbd5e1" rx="10"/><text x="50%" y="52%" text-anchor="middle" font-size="14" fill="#334155" font-family="Arial">NO PHOTO</text></svg>');
            },
            itemPreview(item) {
                if (item.key === '__logo') return this.logoPreview();
                if (item.key === '__photo') return this.photoPreview();
                return '';
            },
            previewText(key) {
                const k = String(key || '');
                const customBrandEnabled = Boolean($wire.get('data.staff_payroll_id_card_use_custom_brand'));
                const customBrandName = String($wire.get('data.staff_payroll_id_card_brand_name') || '').trim();
                const cardTitle = String($wire.get('data.staff_payroll_id_card_title') || '').trim();
                const sample = {
                    '__app_name': customBrandEnabled && customBrandName ? customBrandName : '{{ addslashes((string) config('app.name', 'Universal Hostel Management System')) }}',
                    '__card_title': cardTitle || 'STAFF ID CARD',
                    'full_name': 'Admin User',
                    'job_title': 'Admin',
                    '__label_staff_code': 'Staff Code',
                    '__label_department': 'Department',
                    '__label_email': 'Email',
                    '__label_phone': 'Phone',
                    '__label_joined': 'Joined',
                    'employee_code': 'STAFF-00001',
                    'department': 'All Hostels',
                    'email': 'admin@hostel.com',
                    'phone': '+2348012345678',
                    'joined_on': 'N/A',
                };
                if (k.startsWith('text:')) return k.substring(5) || 'Custom Text';
                return sample[k] || k;
            },
            addBlock() {
                this.items.push(this.normalizeItem({
                    key: this.newField || 'full_name',
                    x: 70,
                    y: 90,
                    size: 14,
                    color: '#ffffff',
                    weight: 500,
                    width: 140,
                    height: 50,
                    locked: false,
                }));
                this.selectedIndex = this.items.length - 1;
                this.sync();
            },
            moveUp(index) {
                if (index <= 0) return;
                [this.items[index - 1], this.items[index]] = [this.items[index], this.items[index - 1]];
                this.selectedIndex = index - 1;
                this.sync();
            },
            moveDown(index) {
                if (index >= this.items.length - 1) return;
                [this.items[index + 1], this.items[index]] = [this.items[index], this.items[index + 1]];
                this.selectedIndex = index + 1;
                this.sync();
            },
            duplicate(index) {
                const source = this.items[index];
                if (!source) return;
                const clone = this.normalizeItem({
                    ...source,
                    _id: this.uid(),
                    x: Number(source.x || 0) + 10,
                    y: Number(source.y || 0) + 10,
                    locked: false,
                });
                this.items.splice(index + 1, 0, clone);
                this.selectedIndex = index + 1;
                this.sync();
            },
            remove(index) {
                this.items.splice(index, 1);
                if (!this.items.length) {
                    this.selectedIndex = null;
                } else if (this.selectedIndex >= this.items.length) {
                    this.selectedIndex = this.items.length - 1;
                }
                this.sync();
            },
            resetDefaults() {
                this.items = this.defaults().map((row) => this.normalizeItem(row));
                this.selectedIndex = this.items.length ? 0 : null;
                this.sync();
            },
            sync() {
                const payload = this.items.map((row) => ({
                    key: String(row.key || 'full_name'),
                    x: Number(row.x ?? 0),
                    y: Number(row.y ?? 0),
                    size: Number(row.size ?? 14),
                    color: String(row.color || '#ffffff'),
                    weight: Number(row.weight ?? 400),
                    width: Number(row.width ?? 140),
                    height: Number(row.height ?? 50),
                    locked: Boolean(row.locked ?? false),
                }));
                $wire.set('data.staff_payroll_id_card_layout_json', payload);
            },
        };
    };
</script>
    @endpush
@endonce
