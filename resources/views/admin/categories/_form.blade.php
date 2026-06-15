<div class="space-y-4">
    <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Название *</label>
        <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required
               class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none">
        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-bold mb-1 text-[#0D7D4C]">Описание</label>
        <textarea name="description" rows="4"
                  class="w-full border-2 border-[#E8FC8C] p-3 rounded-lg focus:border-[#CAF204] focus:outline-none">{{ old('description', $category->description ?? '') }}</textarea>
        @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
</div>