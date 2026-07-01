<div class="space-y-6">
    {{-- Заголовок статьи --}}
    <div class="bg-white p-6 rounded-2xl shadow-lg border-4 border-[#E8FC8C]">
        <label class="block text-lg font-black mb-2 text-[#0D7D4C]">📝 Заголовок статьи *</label>
        <input type="text" name="title" id="article-title" value="{{ old('title', $article->title ?? '') }}" required
               class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none text-lg font-bold">
        @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror

        <label class="flex items-center gap-2 mt-4 cursor-pointer">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $article->is_published ?? false) ? 'checked' : '' }}
                   class="w-5 h-5 accent-[#0D7D4C]">
            <span class="font-bold text-[#422168]">Опубликовать сразу</span>
        </label>
    </div>

    {{-- Панель инструментов конструктора --}}
    <div class="bg-[#422168] p-4 rounded-2xl shadow-lg sticky top-4 z-40">
        <p class="text-[#CAF204] font-bold mb-3 text-sm"> Добавьте блоки в статью:</p>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="addBlock('header')" class="bg-[#CAF204] text-[#422168] px-4 py-2 rounded-lg btn-animated font-bold text-sm">
                📌 Заголовок
            </button>
            <button type="button" onclick="addBlock('text')" class="bg-[#00F3B5] text-[#422168] px-4 py-2 rounded-lg btn-animated font-bold text-sm">
                📝 Текст
            </button>
            <button type="button" onclick="addBlock('image')" class="bg-[#0D7D4C] text-white px-4 py-2 rounded-lg btn-animated font-bold text-sm">
                🖼 Изображение
            </button>
            <button type="button" onclick="openPreviewModal()" class="ml-auto bg-white text-[#422168] px-4 py-2 rounded-lg btn-animated font-bold text-sm">
                👁 Предпросмотр и публикация
            </button>
        </div>
    </div>

    {{-- Контейнер для блоков --}}
    <div id="blocks-container" class="space-y-4">
        {{-- Блоки будут добавляться сюда через JS --}}
    </div>

    {{-- Скрытый контейнер для ошибок валидации --}}
    @if($errors->any())
        <div class="bg-red-100 border-l-8 border-red-500 text-red-700 p-4 rounded-xl">
            <p class="font-bold">⚠️ Исправьте ошибки:</p>
            <ul class="list-disc list-inside text-sm mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

{{-- МОДАЛЬНОЕ ОКНО ПРЕДПРОСМОТРА --}}
<div id="preview-modal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50 p-4">
    <div class="bg-[#E8FC8C] rounded-3xl max-w-3xl w-full max-h-[90vh] overflow-y-auto shadow-2xl border-4 border-[#CAF204]">
        <div class="sticky top-0 bg-[#422168] text-white p-4 rounded-t-3xl flex justify-between items-center">
            <h2 class="text-xl font-black">👁 Предпросмотр статьи</h2>
            <button onclick="closePreviewModal()" class="text-2xl hover:text-[#CAF204]">×</button>
        </div>
        <div class="p-6">
            <h1 id="preview-title" class="text-3xl font-black text-[#422168] mb-6"></h1>
            <div id="preview-content" class="space-y-4"></div>
        </div>
        <div class="sticky bottom-0 bg-white p-4 rounded-b-3xl flex gap-3 border-t-4 border-[#CAF204]">
            <button type="button" onclick="closePreviewModal()" class="flex-1 bg-gray-300 text-[#422168] py-3 rounded-xl btn-animated font-bold">
                ← Вернуться к редактированию
            </button>
            <button type="button" onclick="submitArticleForm()" class="flex-1 bg-[#0D7D4C] text-white py-3 rounded-xl btn-animated font-bold pulse-hover">
                ✅ Да, опубликовать!
            </button>
        </div>
    </div>
</div>

{{-- ШАБЛОНЫ БЛОКОВ (скрытые, клонируются JS) --}}
<template id="tpl-header">
    <div class="block-item bg-white p-5 rounded-2xl shadow-lg border-l-8 border-[#CAF204]" data-type="header">
        <div class="flex justify-between items-center mb-3">
            <span class="bg-[#CAF204] text-[#422168] px-3 py-1 rounded-full text-xs font-black">📌 ЗАГОЛОВОК</span>
            <div class="flex gap-1">
                <button type="button" onclick="moveBlock(this, -1)" class="bg-[#E8FC8C] w-8 h-8 rounded-lg btn-animated" title="Вверх">↑</button>
                <button type="button" onclick="moveBlock(this, 1)" class="bg-[#E8FC8C] w-8 h-8 rounded-lg btn-animated" title="Вниз">↓</button>
                <button type="button" onclick="removeBlock(this)" class="bg-red-500 text-white w-8 h-8 rounded-lg btn-animated" title="Удалить">×</button>
            </div>
        </div>
        <input type="text" class="block-content w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none font-bold text-lg" 
               placeholder="Введите заголовок (макс. 128 символов)" maxlength="128">
        <p class="text-xs text-gray-500 mt-1 counter">0 / 128</p>
        <p class="error-msg text-red-500 text-sm mt-1 hidden"></p>
    </div>
</template>

<template id="tpl-text">
    <div class="block-item bg-white p-5 rounded-2xl shadow-lg border-l-8 border-[#00F3B5]" data-type="text">
        <div class="flex justify-between items-center mb-3">
            <span class="bg-[#00F3B5] text-[#422168] px-3 py-1 rounded-full text-xs font-black">📝 ТЕКСТ</span>
            <div class="flex gap-1">
                <button type="button" onclick="moveBlock(this, -1)" class="bg-[#E8FC8C] w-8 h-8 rounded-lg btn-animated">↑</button>
                <button type="button" onclick="moveBlock(this, 1)" class="bg-[#E8FC8C] w-8 h-8 rounded-lg btn-animated">↓</button>
                <button type="button" onclick="removeBlock(this)" class="bg-red-500 text-white w-8 h-8 rounded-lg btn-animated">×</button>
            </div>
        </div>
        <textarea class="block-content w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none" 
                  rows="4" placeholder="Введите текст (макс. 500 символов)" maxlength="500"></textarea>
        <p class="text-xs text-gray-500 mt-1 counter">0 / 500</p>
        <p class="error-msg text-red-500 text-sm mt-1 hidden"></p>
    </div>
</template>

<template id="tpl-image">
    <div class="block-item bg-white p-5 rounded-2xl shadow-lg border-l-8 border-[#0D7D4C]" data-type="image">
        <div class="flex justify-between items-center mb-3">
            <span class="bg-[#0D7D4C] text-white px-3 py-1 rounded-full text-xs font-black">🖼 ИЗОБРАЖЕНИЕ</span>
            <div class="flex gap-1">
                <button type="button" onclick="moveBlock(this, -1)" class="bg-[#E8FC8C] w-8 h-8 rounded-lg btn-animated">↑</button>
                <button type="button" onclick="moveBlock(this, 1)" class="bg-[#E8FC8C] w-8 h-8 rounded-lg btn-animated">↓</button>
                <button type="button" onclick="removeBlock(this)" class="bg-red-500 text-white w-8 h-8 rounded-lg btn-animated">×</button>
            </div>
        </div>
        <input type="file" class="block-file w-full border-2 border-[#E8FC8C] p-2 rounded-lg" accept="image/jpeg,image/png,image/jpg,image/webp">
        <input type="hidden" class="block-existing-image">
        <div class="image-preview mt-2 hidden">
            <img src="" class="max-h-48 rounded-lg border-2 border-[#CAF204]">
        </div>
        <p class="text-xs text-gray-500 mt-1">JPEG, PNG, WEBP. Макс. 10 МБ.</p>
        <p class="error-msg text-red-500 text-sm mt-1 hidden"></p>
    </div>
</template>

{{-- JS-ЛОГИКА КОНСТРУКТОРА --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация: если редактируем — загрузить существующие блоки
    const existingBlocks = @json($article->blocks ?? []);
    if (existingBlocks.length > 0) {
        existingBlocks.forEach(block => {
            const el = addBlock(block.type, false);
            if (block.type === 'image') {
                el.querySelector('.block-existing-image').value = block.file_path || '';
                if (block.file_path) {
                    const preview = el.querySelector('.image-preview');
                    preview.classList.remove('hidden');
                    preview.querySelector('img').src = '/storage/' + block.file_path;
                }
            } else {
                el.querySelector('.block-content').value = block.content || '';
                updateCounter(el);
            }
        });
    }

    // Делегирование событий для счётчиков символов
    document.getElementById('blocks-container').addEventListener('input', function(e) {
        if (e.target.classList.contains('block-content')) {
            updateCounter(e.target.closest('.block-item'));
        }
        if (e.target.classList.contains('block-file')) {
            const file = e.target.files[0];
            if (file) {
                const preview = e.target.closest('.block-item').querySelector('.image-preview');
                preview.classList.remove('hidden');
                preview.querySelector('img').src = URL.createObjectURL(file);
            }
        }
    });
});

function addBlock(type, scroll = true) {
    const tpl = document.getElementById('tpl-' + type).content.cloneNode(true);
    const container = document.getElementById('blocks-container');
    container.appendChild(tpl);
    const newBlock = container.lastElementChild;
    if (scroll) newBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return newBlock;
}

function removeBlock(btn) {
    if (confirm('Удалить этот блок?')) {
        btn.closest('.block-item').remove();
    }
}

function moveBlock(btn, direction) {
    const block = btn.closest('.block-item');
    const container = document.getElementById('blocks-container');
    if (direction === -1 && block.previousElementSibling) {
        container.insertBefore(block, block.previousElementSibling);
    } else if (direction === 1 && block.nextElementSibling) {
        container.insertBefore(block.nextElementSibling, block);
    }
}

function updateCounter(blockEl) {
    const input = blockEl.querySelector('.block-content');
    const counter = blockEl.querySelector('.counter');
    if (input && counter) {
        const max = input.maxLength;
        counter.textContent = `${input.value.length} / ${max}`;
    }
}

function validateBeforeSubmit() {
    let isValid = true;
    document.querySelectorAll('.block-item').forEach(b => b.querySelector('.error-msg')?.classList.add('hidden'));
    
    const blocks = document.querySelectorAll('.block-item');
    if (blocks.length === 0) {
        alert('Добавьте хотя бы один блок в статью!');
        return false;
    }

    blocks.forEach((block, i) => {
        const type = block.dataset.type;
        const errorMsg = block.querySelector('.error-msg');

        if (type === 'header') {
            const content = block.querySelector('.block-content').value.trim();
            if (!content) {
                errorMsg.textContent = 'Заголовок не может быть пустым.';
                errorMsg.classList.remove('hidden');
                isValid = false;
            } else if (content.length > 128) {
                errorMsg.textContent = `Заголовок слишком длинный (${content.length}/128).`;
                errorMsg.classList.remove('hidden');
                isValid = false;
            }
        }

        if (type === 'text') {
            const content = block.querySelector('.block-content').value.trim();
            if (!content) {
                errorMsg.textContent = 'Текст не может быть пустым.';
                errorMsg.classList.remove('hidden');
                isValid = false;
            } else if (content.length > 500) {
                errorMsg.textContent = `Текст слишком длинный (${content.length}/500).`;
                errorMsg.classList.remove('hidden');
                isValid = false;
            }
        }

        if (type === 'image') {
            const file = block.querySelector('.block-file').files[0];
            const existing = block.querySelector('.block-existing-image').value;
            if (!file && !existing) {
                errorMsg.textContent = 'Загрузите изображение.';
                errorMsg.classList.remove('hidden');
                isValid = false;
            } else if (file && file.size > 10 * 1024 * 1024) {
                errorMsg.textContent = 'Файл больше 10 МБ.';
                errorMsg.classList.remove('hidden');
                isValid = false;
            }
        }
    });

    return isValid;
}

function openPreviewModal() {
    if (!validateBeforeSubmit()) return;

    const title = document.getElementById('article-title').value || 'Без заголовка';
    document.getElementById('preview-title').textContent = title;

    const previewContent = document.getElementById('preview-content');
    previewContent.innerHTML = '';

    document.querySelectorAll('.block-item').forEach(block => {
        const type = block.dataset.type;
        let html = '';

        if (type === 'header') {
            const text = block.querySelector('.block-content').value;
            html = `<h2 class="text-2xl font-black text-[#422168] border-l-8 border-[#CAF204] pl-4">${escapeHtml(text)}</h2>`;
        } else if (type === 'text') {
            const text = block.querySelector('.block-content').value;
            html = `<p class="text-gray-700 leading-relaxed bg-white p-4 rounded-xl">${escapeHtml(text)}</p>`;
        } else if (type === 'image') {
            const file = block.querySelector('.block-file').files[0];
            const existing = block.querySelector('.block-existing-image').value;
            const img = block.querySelector('.image-preview img');
            const src = file ? URL.createObjectURL(file) : (existing ? '/storage/' + existing : '');
            html = `<div class="bg-[#E8FC8C] p-4 rounded-xl"><img src="${src}" class="w-full rounded-lg shadow-lg"></div>`;
        }

        previewContent.insertAdjacentHTML('beforeend', html);
    });

    document.getElementById('preview-modal').classList.remove('hidden');
    document.getElementById('preview-modal').classList.add('flex');
}

function closePreviewModal() {
    document.getElementById('preview-modal').classList.add('hidden');
    document.getElementById('preview-modal').classList.remove('flex');
}

function submitArticleForm() {
    // Собираем блоки в скрытые поля формы
    const form = document.getElementById('article-form');
    form.querySelectorAll('input[name^="blocks"], textarea[name^="blocks"]').forEach(el => el.remove());

    document.querySelectorAll('.block-item').forEach((block, i) => {
        const type = block.dataset.type;
        form.insertAdjacentHTML('beforeend', `<input type="hidden" name="blocks[${i}][type]" value="${type}">`);

        if (type === 'header' || type === 'text') {
            const content = block.querySelector('.block-content').value;
            form.insertAdjacentHTML('beforeend', `<input type="hidden" name="blocks[${i}][content]" value="${escapeHtml(content)}">`);
        }

        if (type === 'image') {
            const existing = block.querySelector('.block-existing-image').value;
            if (existing) {
                form.insertAdjacentHTML('beforeend', `<input type="hidden" name="blocks[${i}][existing_image]" value="${existing}">`);
            }
            // Файл переносим в форму с правильным именем
            const fileInput = block.querySelector('.block-file');
            if (fileInput.files[0]) {
                const dt = new DataTransfer();
                dt.items.add(fileInput.files[0]);
                const newInput = document.createElement('input');
                newInput.type = 'file';
                newInput.name = `blocks[${i}][file]`;
                newInput.files = dt.files;
                newInput.style.display = 'none';
                form.appendChild(newInput);
            }
        }
    });

    form.submit();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/"/g, '&quot;');
}
</script>