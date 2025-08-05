<x-app>
    <div x-data="{ showUploadModal: false, showFolderModal: false }" class="m-3 px-4 sm:px-6 lg:px-8 bg-white rounded-3xl shadow-lg">
        <div x-data="{ fileId: (() => { const segments = window.location.pathname.split('/').filter(Boolean); return segments.length > 0 ? segments.pop() : '0'; })() }">
            <div x-show="showUploadModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-20" style="display: none">
                <div class="bg-white p-4 border rounded-lg shadow-lg" @click.away="showUploadModal = false">
                    <p>Upload File(s):</p>
                    <form method="POST" action="{{ route('file.store') }}" id="uploadForm"
                        enctype="multipart/form-data">
                        @csrf
                        <input name="uuid" type="text" hidden :value="fileId">
                        <input type="file" name="files[]" id='fileInput' class="p-2" id="files" multiple>
                        <div class="flex justify-end py-2 gap-2">
                            <button type="submit" class="bg-gray-100 rounded-lg hover:bg-gray-200 p-1">
                                <x-icons.upload />
                            </button>
                            <button type="button" @click="showUploadModal = false"
                                class="bg-gray-100 rounded-lg hover:bg-gray-200 p-1">
                                <x-icons.cancel />
                            </button>
                        </div>
                    </form>
                    <div
                        class="mt-2 outline-2 outline-green-400 outline-dashed rounded-full h-7 w-full flex items-center justify-start">
                        <div class=" rounded-full h-5 mx-1 bg-gradient-to-r from-green-300 to-green-400 transition-all duration-500 ease-in-out"
                            style="width: 0%" id="progressBar">
                        </div>
                    </div>
                    <p class="pl-2 pt-2" id="progressInfo">...</p>
                </div>
            </div>

            <div x-show="showFolderModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-20"
                style="display: none">
                <div class="bg-white p-4 w-80 border rounded-lg shadow-lg" @click.away="showFolderModal = false">
                    <p>Create a new Folder:</p>
                    <form method="POST" action="{{ route('file.folder') }}">
                        @csrf
                        <input name="uuid" type="text" hidden :value="fileId">
                        <input name="filename" class="border-2 rounded-lg my-2 p-1 w-full" placeholder="Enter File Name"
                            type="text">
                        <div class="flex justify-end py-2 gap-2">
                            <button type="submit" class="bg-gray-100 rounded-lg hover:bg-gray-200 p-1">
                                <x-icons.upload />
                            </button>
                            <button @click="showFolderModal = false"
                                class="bg-gray-100 rounded-lg hover:bg-gray-200 p-1">
                                <x-icons.cancel />
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-data="{ deleteMode: false, submitForm: false, selected: [] }">
            <div class="sticky top-0 z-10">
                <div class="flex items-center bg-white rounded-lg py-7">
                    <div x-show='!deleteMode || submitForm'
                        x-transition:enter="transition ease-out duration-300 delay-100"
                        x-transition:enter-start="opacity-0 transform translate-x-2"
                        x-transition:enter-end="opacity-100 transform translate-x-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 transform translate-x-0"
                        x-transition:leave-end="opacity-0 transform translate-x-2"
                        class="absolute flex items-center gap-x-2">
                        <button @click="showUploadModal = true" class="bg-gray-200 rounded-lg hover:bg-gray-300 p-1">
                            <x-icons.add />
                        </button>

                        <button @click="showFolderModal = true" class="bg-gray-200 rounded-lg hover:bg-gray-300 p-1">
                            <x-icons.add-folder />
                        </button>

                        <div x-data="{ show: false }" class="flex items-center gap-x-2">
                            <div class="bg-gray-200 w-[2px] h-7 m-1"></div>

                            <button @click="show = true" class="bg-gray-200 rounded-lg hover:bg-gray-300 p-1">
                                <x-icons.sort />
                            </button>

                            <div 
                                x-show="show" style="display: none"
                                class="fixed inset-0"
                                @click="show = false"
                            >
                            </div>
                            <div 
                                x-show="show" style="display: none"
                                class="absolute translate-x-14 translate-y-28 bg-white text-lg rounded px-3 shadow-xl border-2 border-gray-500"
                            >
                                <div class="flex flex-col items-start gap-y-2 py-2 w-32">
                                    <p class="text-gray-500 text-sm break-all">sort elements</p>
                                    <div class="flex gap-x-2">
                                        <p class="min-w-12">Name</p>
                                        <button @click="window.dispatchEvent(new CustomEvent('sort-files', { detail: { field: 'filename', dir: 'asc' } })); show = false" 
                                            class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 h-8"><x-icons.diag-down-arrow /></button>
                                        <button @click="window.dispatchEvent(new CustomEvent('sort-files', { detail: { field: 'filename', dir: 'desc' } })); show = false" 
                                            class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 h-8 transform rotate-180"><x-icons.diag-down-arrow /></button>
                                    </div>
                                    <div class="flex gap-x-2">
                                        <p class="min-w-12">Date</p>
                                        <button @click="window.dispatchEvent(new CustomEvent('sort-files', { detail: { field: 'created_at', dir: 'asc' } })); show = false" 
                                            class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 h-8"><x-icons.diag-down-arrow /></button>
                                        <button @click="window.dispatchEvent(new CustomEvent('sort-files', { detail: { field: 'created_at', dir: 'desc' } })); show = false" 
                                            class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 h-8 transform rotate-180"><x-icons.diag-down-arrow /></button>
                                    </div>
                                    <div class="flex gap-x-2">
                                        <p class="min-w-12">Size</p>
                                        <button @click="window.dispatchEvent(new CustomEvent('sort-files', { detail: { field: 'file_size', dir: 'asc' } })); show = false" 
                                            class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 h-8"><x-icons.diag-down-arrow /></button>
                                        <button @click="window.dispatchEvent(new CustomEvent('sort-files', { detail: { field: 'file_size', dir: 'desc' } })); show = false" 
                                            class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 h-8 transform rotate-180"><x-icons.diag-down-arrow /></button>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-200 w-[2px] h-7 m-1"></div>

                            <button @click="deleteMode = true" class="bg-gray-200 rounded-lg hover:bg-gray-300 p-1">
                                <x-icons.trash />
                            </button>
                        </div>
                    </div>

                    <div x-show='deleteMode && !submitForm' style="display: none"
                        x-transition:enter="transition ease-out duration-300 delay-100"
                        x-transition:enter-start="opacity-0 transform -translate-x-2"
                        x-transition:enter-end="opacity-100 transform translate-x-0"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 transform translate-x-0"
                        x-transition:leave-end="opacity-0 transform -translate-x-2" class="absolute pt-1 pb-0.5">

                        <form method="POST" action="{{ route('file.delete') }}">
                            @csrf
                            <input type="text" name="ids" hidden :value="JSON.stringify(selected)">
                            <div class="flex items-center gap-x-2">
                                <div class="px-4 flex items-center justify-center">
                                    <div x-show="selected.length === 0" class="absolute opacity-60">
                                        <button type='button' class="bg-red-200 hover:bg-red-200 rounded-lg p-1">
                                            <x-icons.trash />
                                        </button>
                                    </div>
                                    <div x-show="selected.length !== 0"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-60" x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition ease-out duration-200"
                                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                        class="absolute">
                                        <button type='submit' @click="submitForm = true"
                                            class="bg-red-200 hover:bg-red-300 rounded-lg p-1">
                                            <x-icons.trash />
                                        </button>
                                    </div>
                                </div>

                                <div class="bg-gray-200 w-[2px] h-7 m-1"></div>

                                <button type='button' @click="deleteMode = false"
                                    class="bg-gray-200 rounded-lg hover:bg-gray-300 p-1">
                                    <x-icons.cancel-o />
                                </button>

                                <div x-data="{ all: false }" x-init="$watch('deleteMode', value => { if (!value) all = false, window.all = false })" class="p-3" @click="all = !all, toggleAll()">
                                    <input type="checkbox" class="sr-only peer" x-model="all"
                                        :checked="all" />
                                    <div
                                        class="w-3 h-3 border-2 p-1 border-gray-400 rounded-full peer-checked:bg-red-500 peer-checked:border-red-500">
                                    </div>
                                </div>

                                <div class="flex gap-x-2 items-center">
                                    <span class="font-bold" x-text="selected.length"></span>
                                    <p class="text-sm">selected</p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="bg-white border-b-[2px] border-dashed"></div>
                <div x-data='{"path": @json($path)}' class="bg-white py-2 px-1">
                    <!-- Scrollable breadcrumb container -->
                    <div class="flex flex-row-reverse items-center overflow-x-auto whitespace-nowrap"
                        style="scrollbar-width: none; -ms-overflow-style: none;">

                        <div class="outline-dotted outline-gray-300 outline-2 ml-2 h-0.5 my-1 flex flex-grow min-w-0">
                        </div>

                        @foreach ($path as $item)
                            @if ($loop->first)
                                <a class="rounded-md bg-green-100 cursor-not-allowed pointer-events-none px-1">
                                    {{ $item['filename'] }}
                                </a>
                                <x-icons.arrow-right />
                            @else
                                <a href="{{ $item['uuid'] }}" class="rounded-md hover:bg-slate-200 px-1">
                                    {{ $item['filename'] }}
                                </a>
                                <x-icons.arrow-right />
                            @endif
                        @endforeach
                        @auth
                            <a class="rounded-md hover:bg-slate-200 px-1" href="/">root</a>
                        @endauth               
                        @guest
                            <a class="rounded-md hover:bg-slate-200 px-1" @click="alert('The folder you\'re viewing is shared, you have access to everything inside it (view only mode) and anything above it is hidden from you.')">shared</a>
                        @endguest
                    </div>
                </div>
                <div class="border-b-[2px] border-dashed"></div>
            </div>

            <div class="min-h-96 py-2 {{ count($files) === 0 ? 'flex items-center justify-center' : '' }}"
                x-data='{"files": @json($files)}'
                x-init="
                    window.addEventListener('sort-files', e => {
                        let { field, dir } = e.detail;
                        files.sort((a, b) => {
                            let valA = a[field];
                            let valB = b[field];

                            // Handle nulls and undefined
                            if (valA == null) return 1;
                            if (valB == null) return -1;

                            // Normalize string casing for name
                            if (typeof valA === 'string') valA = valA.toLowerCase();
                            if (typeof valB === 'string') valB = valB.toLowerCase();

                            // Date parsing if sorting by created_at
                            if (field === 'created_at') {
                                valA = new Date(valA);
                                valB = new Date(valB);
                            }

                            let result = 0;
                            if (valA < valB) result = -1;
                            else if (valA > valB) result = 1;

                            return dir === 'desc' ? -result : result;
                        });
                    });
                ">
                <p class="text-gray-500" x-show="files.length === 0">Nothing's here...</p>
                <div class="flex flex-col gap-2" x-show="files.length > 0">
                    <template x-for="file in files" :key="file['uuid']">
                        <div 
                            x-data="{ checked: false, show: false }"
                            x-on:checkbox-external-update.window="checked = $event.detail"
                            
                            x-init="$watch('deleteMode', value => { if (!value) checked = false })"
                            class="flex items-center gap-2 rounded-md p-2 hover:bg-gray-200 cursor-pointer"
                        >
                            <div class="flex items-center gap-2 w-full"
                                @click="if (deleteMode) checked = !checked; else window.location.href = (file['is_folder'] ? '/' : '/v/') + file['uuid']"
                            >
                                <div x-show='deleteMode' x-transition:enter="transition ease-out duration-1000"
                                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition ease-in duration-0"
                                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                    x-init="$watch('checked', value => { if (value) { selected.push(file['uuid']) } else { selected = selected.filter(i => i !== file['uuid']) } })">
                                    <div class="pr-2">
                                        <input type="checkbox" class="sr-only peer" :checked="checked"
                                            :value="file['uuid']" @change="checked = $event.target.checked" />
                                        <div
                                            class="w-3 h-3 border-2 p-1 border-gray-400 rounded-full peer-checked:bg-red-500 peer-checked:border-red-500">
                                        </div>
                                    </div>
                                </div>
                                <div class="scale-125" x-show="file['is_folder']">
                                    <x-icons.folder />
                                </div>
                                <div class="bg-white" x-show="!file['is_folder']">
                                    <img loading="lazy"
                                        :src="'file-type-icons/s-' + getFileExtension(file['filename']) + '.svg'"
                                        class="w-6 h-6 scale-[2] bg-transparent fill-none" style="clip-path: inset(15%)"
                                        onerror="this.onerror=null;this.src='file-type-icons/s-default.svg'" />
                                </div>
                                <div class="flex flex-col w-full gap-1 pl-2">
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium break-words break-all leading-none" x-text="file['filename']"></p>
                                        <p x-show="file['is_shared']" class="font-semibold text-sm text-red-500 break-words break-all leading-none">— SHARED</p>
                                    </div>
                                    <div class="flex flex-row-reverse justify-between">
                                        <div class="text-gray-400 text-sm w-20 text-right" x-text="fileSize(file['file_size'])">
                                        </div>
                                        <p class="text-gray-400 text-sm"
                                            x-text="new Date(file['created_at']).toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' })">
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div x-show="!deleteMode"
                                x-intersect:leave="show = false"
                                x-transition:enter="transition ease-out duration-1000"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-0"
                                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            >
                                <div 
                                    x-show="show" 
                                    class="fixed inset-0"
                                    @click="show = false"
                                >
                                </div>
                                <div 
                                    x-show="show"
                                    class="absolute -translate-x-full bg-white text-lg rounded px-3 py-1 shadow-xl border-2 border-gray-500"
                                >
                                    <div class="flex flex-col items-start gap-y-2 py-2 w-32">

                                        {{-- Folder Tab --}}
                                        <p class="text-gray-500 text-sm break-all" x-text="file['filename']"></p>

                                        <form x-show="file['is_folder']" method="POST" action="{{ route('folder.share') }}">
                                            @csrf
                                            <input name="uuid" type="text" hidden :value="file['uuid']">
                                            <button class="bg-red-100 rounded-lg border border-slate-400 hover:bg-red-200 px-1 min-w-32 pl-1">
                                                <p x-show="!file['is_shared']">Share Folder</p>
                                                <p x-show="file['is_shared']" class="text-left pl-2">Unshare</p>
                                            </button>
                                        </form>

                                        <a :href="'/' + file['uuid']" x-show="file['is_folder']" class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 min-w-32 pl-3">View Folder</a>
                                        <a @click="navigator.clipboard.writeText(window.location.href.split('?')[0].replace(/\/[^\/]*$/, '') + '/' + file['uuid']); show = false;" x-show="file['is_folder'] && file['is_shared']" class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 min-w-32 pl-3">Copy Link</a>

                                        {{-- File Tab --}}
                                        <a :href="/v/ + file['uuid']" x-show="!file['is_folder']" class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 min-w-32 pl-3">View File</a>
                                        <a :href="/d/ + file['uuid']" x-show="!file['is_folder']" class="bg-gray-100 rounded-lg border border-slate-400 hover:bg-gray-200 px-1 min-w-32 pl-3">Download</a>
                                    </div>
                                </div>
                                <div class="w-full" @click="show = true">
                                    <x-icons.more />
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        const input = document.getElementById('fileInput');

        input.addEventListener('change', () => {
            const maxSize = 100 * 1024 * 1024;
            for (let file of input.files) {
                if (file.size > maxSize) {
                    alert(`${file.name} is too big`);
                    input.value = '';
                    break;
                }
            }
        });

        const form = document.getElementById('uploadForm');
        const progressBar = document.getElementById('progressBar');
        const progressInfo = document.getElementById('progressInfo');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const files = input.files;
            if (!files.length) return;

            const csrfToken = document.querySelector('input[name="_token"]').value;
            const uuid = document.querySelector('input[name="uuid"]').value;

            const formData = new FormData();
            formData.append('_token', csrfToken);
            formData.append('uuid', uuid);

            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            const xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = (e.loaded / e.total) * 100;
                    progressBar.style.width = (percent / 100) ** 3 * 100 + "%"
                    progressInfo.innerHTML = "Uploading..."
                    if (percent == 100)
                        progressInfo.innerHTML = "Storing uploads..."
                }
            });

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        location.reload();
                    } else {
                        alert('Upload failed');
                    }
                }
            };

            xhr.open('POST', form.action);
            xhr.send(formData);
        })
    </script>
</x-app>
