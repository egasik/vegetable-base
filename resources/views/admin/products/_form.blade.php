<div class="space-y-4">
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
                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('category_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Цена (₽) *</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $product->price ?? '') }}" required
               class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none">
        @error('price') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Описание *</label>
        <textarea name="description" rows="5" required
                  class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
</div>