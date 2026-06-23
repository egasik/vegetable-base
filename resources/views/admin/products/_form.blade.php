<div class="space-y-6">
    <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Название *</label>
        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}" required
               class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none">
        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Категория *</label>
        <select name="category_id" required class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none">
            <option value="">-- Выберите --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        @error('category_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-[#E8FC8C]/30 p-4 rounded-xl border-2 border-[#E8FC8C]">
        <!-- Блок Розницы -->
        <div>
            <label class="flex items-center gap-2 mb-3 cursor-pointer">
                <input type="hidden" name="is_retail" value="0">
                <input type="checkbox" name="is_retail" value="1" {{ old('is_retail', $product->is_retail ?? true) ? 'checked' : '' }} 
                       class="w-5 h-5 accent-[#0D7D4C]" id="check_retail">
                <span class="font-bold text-[#422168]">🛒 Розничная продажа (за кг)</span>
            </label>
            <div id="retail_fields">
                <label class="block text-xs font-bold mb-1 text-[#0D7D4C]">Цена за 1 кг (₽)</label>
                <input type="number" step="0.01" name="retail_price" value="{{ old('retail_price', $product->retail_price ?? '') }}" 
                       class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
                @error('retail_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Блок Опта -->
        <div>
            <label class="flex items-center gap-2 mb-3 cursor-pointer">
                <input type="hidden" name="is_wholesale" value="0">
                <input type="checkbox" name="is_wholesale" value="1" {{ old('is_wholesale', $product->is_wholesale ?? false) ? 'checked' : '' }} 
                       class="w-5 h-5 accent-[#422168]" id="check_wholesale">
                <span class="font-bold text-[#422168]"> Оптовая продажа (мешками)</span>
            </label>
            <div id="wholesale_fields" class="{{ old('is_wholesale', $product->is_wholesale ?? false) ? '' : 'opacity-50' }}">
                <div class="flex gap-2 mb-2">
                    <div class="flex-1">
                        <label class="block text-xs font-bold mb-1 text-[#0D7D4C]">Вес мешка (кг)</label>
                        <select name="wholesale_unit_kg" class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
                            <option value="10" {{ old('wholesale_unit_kg', $product->wholesale_unit_kg ?? '') == 10 ? 'selected' : '' }}>10 кг</option>
                            <option value="20" {{ old('wholesale_unit_kg', $product->wholesale_unit_kg ?? '') == 20 ? 'selected' : '' }}>20 кг</option>
                            <option value="50" {{ old('wholesale_unit_kg', $product->wholesale_unit_kg ?? '') == 50 ? 'selected' : '' }}>50 кг</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold mb-1 text-[#0D7D4C]">Цена за мешок (₽)</label>
                        <input type="number" step="0.01" name="wholesale_price" value="{{ old('wholesale_price', $product->wholesale_price ?? '') }}" 
                               class="w-full border-2 border-[#E8FC8C] p-2 rounded-lg focus:border-[#CAF204] focus:outline-none">
                    </div>
                </div>
                @error('wholesale_unit_kg') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                @error('wholesale_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>
        <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]"> Фотография товара</label>
        <input type="file" name="image" accept="image/jpeg,image/png,image/jpg,image/webp" 
               class="block w-full text-sm text-[#422168] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-bold file:bg-[#CAF204] file:text-[#422168] hover:file:bg-[#00F3B5] cursor-pointer btn-animated border-2 border-[#E8FC8C] p-2 rounded-lg">
        <p class="text-xs text-gray-500 mt-1">Форматы: JPEG, PNG, WEBP. Макс. размер: 10 МБ.</p>
        @error('image') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
        
        @if(isset($product) && $product->image_path)
            <div class="mt-3">
                <p class="text-xs font-bold text-[#0D7D4C] mb-1">Текущее фото:</p>
                <img src="{{ asset('storage/' . $product->image_path) }}" class="h-32 w-32 object-cover rounded-lg border-2 border-[#CAF204]">
            </div>
        @endif
    </div>
    <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Описание *</label>
        <textarea name="description" rows="5" required
                  class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
</div>

<script>
    // Простой JS для визуального включения/выключения полей
    document.getElementById('check_retail').addEventListener('change', function() {
        document.getElementById('retail_fields').style.opacity = this.checked ? '1' : '0.5';
    });
    document.getElementById('check_wholesale').addEventListener('change', function() {
        document.getElementById('wholesale_fields').style.opacity = this.checked ? '1' : '0.5';
    });
</script>