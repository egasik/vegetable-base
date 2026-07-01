@extends('layouts.app')
@section('title', 'Оплата заказа')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-4xl font-black text-[#422168] text-center mb-8">Оплата заказа №{{ $order->id }}</h1>

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-6 rounded-xl">
            <ul class="text-red-700 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Левая колонка: Адрес доставки --}}
        <div class="space-y-4">
            <div class="bg-white p-6 rounded-3xl shadow-xl border-4 border-[#E8FC8C]">
                <h3 class="text-2xl font-black text-[#422168] mb-4"> Адрес доставки</h3>
                
                <form action="{{ route('payment.process', $order) }}" method="POST" id="payment-form">
                    @csrf
                    
                    {{-- Модульные поля адреса --}}
                    <div class="space-y-4">
                        {{-- Населённый пункт --}}
                        <div>
                            <label class="block text-sm font-bold text-[#0D7D4C] mb-1">
                                Населённый пункт <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="city-input" 
                                   autocomplete="off"
                                   placeholder="Начните вводить (например, Иркутск)"
                                   class="w-full border-2 border-[#E8FC8C] p-3 rounded-xl focus:border-[#CAF204] focus:outline-none">
                            <div id="city-dropdown" class="hidden mt-1 max-h-48 overflow-y-auto border-2 border-[#CAF204] rounded-xl bg-white shadow-lg z-50"></div>
                            <input type="hidden" id="city-value" name="delivery_city" value="{{ old('delivery_city', $order->delivery_city ?? '') }}" required>
                            @error('delivery_city')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Улица --}}
                        <div>
                            <label class="block text-sm font-bold text-[#0D7D4C] mb-1">
                                Улица <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="street-input" 
                                   autocomplete="off"
                                   placeholder="Сначала выберите населённый пункт"
                                   disabled
                                   class="w-full border-2 border-gray-300 bg-gray-100 p-3 rounded-xl text-gray-500 cursor-not-allowed">
                            <div id="street-dropdown" class="hidden mt-1 max-h-48 overflow-y-auto border-2 border-[#CAF204] rounded-xl bg-white shadow-lg z-50"></div>
                            <input type="hidden" id="street-value" name="delivery_street" value="{{ old('delivery_street', '') }}" required>
                            @error('delivery_street')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Номер дома --}}
                        <div>
                            <label class="block text-sm font-bold text-[#0D7D4C] mb-1">
                                Номер дома <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   id="house-input" 
                                   autocomplete="off"
                                   placeholder="Сначала выберите улицу"
                                   disabled
                                   class="w-full border-2 border-gray-300 bg-gray-100 p-3 rounded-xl text-gray-500 cursor-not-allowed">
                            <div id="house-dropdown" class="hidden mt-1 max-h-48 overflow-y-auto border-2 border-[#CAF204] rounded-xl bg-white shadow-lg z-50"></div>
                            <input type="hidden" id="house-value" name="delivery_house" value="{{ old('delivery_house', '') }}" required>
                            @error('delivery_house')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Статус валидации --}}
                        <div id="validation-status" class="hidden p-3 rounded-xl text-sm font-bold"></div>
                    </div>

                    {{-- Карта --}}
                    <div class="mt-6">
                        <label class="block text-sm font-bold text-[#0D7D4C] mb-2">Или выберите на карте</label>
                        <div id="map" class="w-full h-80 rounded-xl border-2 border-[#E8FC8C]"></div>
                        
                    </div>

                    {{-- Скрытые поля для координат и полного адреса --}}
                    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude', $order->latitude ?? '') }}">
                    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude', $order->longitude ?? '') }}">
                    <input type="hidden" id="full-address" name="delivery_address" value="{{ old('delivery_address', $order->delivery_address ?? '') }}">

                    {{-- Кнопка отправки --}}
                    <div class="mt-6">
                        <button type="submit" 
                                id="submit-btn"
                                disabled
                                class="w-full bg-gray-300 text-gray-500 font-bold py-3 rounded-xl cursor-not-allowed">
                            Сначала укажите адрес доставки
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Правая колонка: Информация о заказе --}}
        <div class="space-y-4">
            <div class="bg-white p-6 rounded-3xl shadow-xl border-4 border-[#E8FC8C]">
                <h3 class="text-2xl font-black text-[#422168] mb-4">Информация о заказе</h3>
                
                <div class="space-y-2 mb-6">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Сумма:</span>
                        <span class="font-bold text-[#422168]">{{ number_format($order->total_amount, 0, '.', ' ') }} ₽</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Товаров:</span>
                        <span class="font-bold">{{ $order->items->count() }} шт.</span>
                    </div>
                </div>

                <div class="p-4 bg-[#E8FC8C]/30 rounded-xl border-l-4 border-[#CAF204]">
                    <p class="text-sm text-[#422168]">
                        <strong>Важно:</strong> Доставка осуществляется по Иркутску и Иркутской области. 
                        Адрес будет проверен через карту.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Подключение Yandex Maps API --}}
<script src="https://api-maps.yandex.ru/2.1/?apikey={{ config('services.yandex.maps_js_api_key') }}&lang=ru_RU" type="text/javascript"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let myMap;
    let placemark;
    let cityTimeout, streetTimeout, houseTimeout;
    let isAddressValid = false;

    // Инициализация карты
    if (typeof ymaps !== 'undefined') {
        ymaps.ready(initMap);
    } else {
        console.error('Yandex Maps API не загружен!');
    }
    
    function initMap() {
        myMap = new ymaps.Map("map", {
            center: [52.2978, 104.2964], // Иркутск
            zoom: 12,
            controls: ['zoomControl']
        });

        // Клик по карте
        myMap.events.add('click', function(e) {
            const coords = e.get('coords');
            
            ymaps.geocode(coords, {
                results: 1,
                strictBounds: true,
                boundedBy: new ymaps.bounds([51.0, 102.0], [54.0, 107.0])
            }).then(function(res) {
                const firstGeoObject = res.geoObjects.get(0);
                if (firstGeoObject) {
                    const address = firstGeoObject.getAddressLine();
                    const addressParts = firstGeoObject.properties.get('Address', {});
                    
                    document.getElementById('city-input').value = addressParts.locality || '';
                    document.getElementById('city-value').value = addressParts.locality || '';
                    document.getElementById('street-input').value = addressParts.street || '';
                    document.getElementById('street-value').value = addressParts.street || '';
                    document.getElementById('house-input').value = addressParts.premiseNumber || '';
                    document.getElementById('house-value').value = addressParts.premiseNumber || '';
                    
                    setMapMarker(coords, address);
                    validateFullAddress();
                }
            });
        });

        // Если у заказа уже есть координаты
        @if($order->latitude && $order->longitude)
            const savedCoords = [{{ $order->latitude }}, {{ $order->longitude }}];
            ymaps.geocode(savedCoords, { results: 1 }).then(function(res) {
                const firstGeoObject = res.geoObjects.get(0);
                if (firstGeoObject) {
                    setMapMarker(savedCoords, firstGeoObject.getAddressLine());
                }
            });
        @endif
    }

    function setMapMarker(coords, address) {
        if (placemark) {
            myMap.geoObjects.remove(placemark);
        }
        
        placemark = new ymaps.Placemark(coords, {
            hintContent: address,
            balloonContent: address
        });
        
        myMap.geoObjects.add(placemark);
        myMap.setCenter(coords, 16);
        
        document.getElementById('latitude').value = coords[0];
        document.getElementById('longitude').value = coords[1];
        document.getElementById('full-address').value = address;
    }

    // Автодополнение для населённого пункта
    document.getElementById('city-input').addEventListener('input', function() {
        clearTimeout(cityTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            document.getElementById('city-dropdown').classList.add('hidden');
            return;
        }

        cityTimeout = setTimeout(() => {
            fetch(`{{ route('api.address.cities') }}?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Города:', data);
                    showDropdown('city-dropdown', data, 'city');
                })
                .catch(error => console.error('Ошибка:', error));
        }, 300);
    });

    // Автодополнение для улицы
    document.getElementById('street-input').addEventListener('input', function() {
        clearTimeout(streetTimeout);
        const query = this.value.trim();
        const city = document.getElementById('city-value').value;
        
        if (!city || query.length < 2) {
            document.getElementById('street-dropdown').classList.add('hidden');
            return;
        }

        streetTimeout = setTimeout(() => {
            fetch(`{{ route('api.address.streets') }}?city=${encodeURIComponent(city)}&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Улицы:', data);
                    showDropdown('street-dropdown', data, 'street');
                })
                .catch(error => console.error('Ошибка:', error));
        }, 300);
    });

    // Автодополнение для дома
    document.getElementById('house-input').addEventListener('input', function() {
        clearTimeout(houseTimeout);
        const query = this.value.trim();
        const city = document.getElementById('city-value').value;
        const street = document.getElementById('street-value').value;
        
        if (!city || !street) {
            document.getElementById('house-dropdown').classList.add('hidden');
            return;
        }

        houseTimeout = setTimeout(() => {
            fetch(`{{ route('api.address.houses') }}?city=${encodeURIComponent(city)}&street=${encodeURIComponent(street)}&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Дома:', data);
                    showDropdown('house-dropdown', data, 'house');
                })
                .catch(error => console.error('Ошибка:', error));
        }, 300);
    });

    function showDropdown(dropdownId, data, type) {
        const dropdown = document.getElementById(dropdownId);
        
        if (data.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }

        dropdown.innerHTML = data.map(item => `
            <div class="p-3 hover:bg-[#E8FC8C] cursor-pointer border-b border-gray-200 last:border-b-0"
                 onclick="selectItem('${type}', '${item.name.replace(/'/g, "\\'")}', '${item.full_address.replace(/'/g, "\\'")}', ${item.latitude || 'null'}, ${item.longitude || 'null'})">
                <div class="font-bold text-sm text-[#422168]">${item.name}</div>
                <div class="text-xs text-gray-500">${item.full_address}</div>
            </div>
        `).join('');
        
        dropdown.classList.remove('hidden');
    }

    window.selectItem = function(type, name, fullAddress, lat, lng) {
        console.log('Выбрано:', type, name, lat, lng);
        
        if (type === 'city') {
            document.getElementById('city-input').value = name;
            document.getElementById('city-value').value = name;
            document.getElementById('city-dropdown').classList.add('hidden');
            
            const streetInput = document.getElementById('street-input');
            streetInput.disabled = false;
            streetInput.classList.remove('border-gray-300', 'bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
            streetInput.classList.add('border-[#E8FC8C]', 'bg-white', 'text-gray-900', 'cursor-text');
            streetInput.focus();
            
            document.getElementById('street-input').value = '';
            document.getElementById('street-value').value = '';
            document.getElementById('house-input').value = '';
            document.getElementById('house-value').value = '';
            document.getElementById('house-input').disabled = true;
            
        } else if (type === 'street') {
            document.getElementById('street-input').value = name;
            document.getElementById('street-value').value = name;
            document.getElementById('street-dropdown').classList.add('hidden');
            
            const houseInput = document.getElementById('house-input');
            houseInput.disabled = false;
            houseInput.classList.remove('border-gray-300', 'bg-gray-100', 'text-gray-500', 'cursor-not-allowed');
            houseInput.classList.add('border-[#E8FC8C]', 'bg-white', 'text-gray-900', 'cursor-text');
            houseInput.focus();
            
            document.getElementById('house-input').value = '';
            document.getElementById('house-value').value = '';
            
        } else if (type === 'house') {
            document.getElementById('house-input').value = name;
            document.getElementById('house-value').value = name;
            document.getElementById('house-dropdown').classList.add('hidden');
            
            if (lat && lng) {
                setMapMarker([lat, lng], fullAddress);
            }
        }

        validateFullAddress();
    };

    function validateFullAddress() {
        const city = document.getElementById('city-value').value;
        const street = document.getElementById('street-value').value;
        const house = document.getElementById('house-value').value;
        
        console.log('Валидация:', { city, street, house });
        
        if (!city || !street || !house) {
            updateValidationStatus(false, 'Заполните все поля адреса');
            updateSubmitButton(false);
            return;
        }

        fetch('{{ route('api.address.validate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ city, street, house })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Результат валидации:', data);
            if (data.valid) {
                updateValidationStatus(true, '✓ Адрес подтверждён: ' + data.full_address);
                document.getElementById('latitude').value = data.latitude;
                document.getElementById('longitude').value = data.longitude;
                document.getElementById('full-address').value = data.full_address;
                updateSubmitButton(true);
                
                if (data.latitude && data.longitude) {
                    setMapMarker([data.latitude, data.longitude], data.full_address);
                }
            } else {
                updateValidationStatus(false, '✗ ' + data.message);
                updateSubmitButton(false);
            }
        })
        .catch(error => {
            console.error('Ошибка валидации:', error);
            updateValidationStatus(false, 'Ошибка проверки адреса');
            updateSubmitButton(false);
        });
    }

    function updateValidationStatus(valid, message) {
        const statusDiv = document.getElementById('validation-status');
        statusDiv.classList.remove('hidden');
        
        if (valid) {
            statusDiv.className = 'p-3 rounded-xl text-sm font-bold bg-green-100 text-green-800 border-l-4 border-green-500';
        } else {
            statusDiv.className = 'p-3 rounded-xl text-sm font-bold bg-yellow-100 text-yellow-800 border-l-4 border-yellow-500';
        }
        
        statusDiv.textContent = message;
    }

    function updateSubmitButton(enabled) {
        const submitBtn = document.getElementById('submit-btn');
        if (enabled) {
            submitBtn.disabled = false;
            submitBtn.className = 'w-full bg-[#0D7D4C] text-white font-bold py-3 rounded-xl btn-animated pulse-hover';
            submitBtn.textContent = 'Оплатить {{ number_format($order->total_amount, 0, '.', ' ') }} ₽';
        } else {
            submitBtn.disabled = true;
            submitBtn.className = 'w-full bg-gray-300 text-gray-500 font-bold py-3 rounded-xl cursor-not-allowed';
            submitBtn.textContent = 'Сначала укажите адрес доставки';
        }
    }

    // Закрытие dropdown при клике вне
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#city-input') && !e.target.closest('#city-dropdown')) {
            document.getElementById('city-dropdown').classList.add('hidden');
        }
        if (!e.target.closest('#street-input') && !e.target.closest('#street-dropdown')) {
            document.getElementById('street-dropdown').classList.add('hidden');
        }
        if (!e.target.closest('#house-input') && !e.target.closest('#house-dropdown')) {
            document.getElementById('house-dropdown').classList.add('hidden');
        }
    });

    // Валидация перед отправкой
    document.getElementById('payment-form').addEventListener('submit', function(e) {
        const city = document.getElementById('city-value').value;
        const street = document.getElementById('street-value').value;
        const house = document.getElementById('house-value').value;
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        
        console.log('Отправка формы:', { city, street, house, latitude, longitude });
        
        if (!city || !street || !house) {
            e.preventDefault();
            alert('Пожалуйста, заполните все поля адреса');
            return false;
        }
        
        if (!latitude || !longitude) {
            e.preventDefault();
            alert('Пожалуйста, дождитесь подтверждения адреса');
            return false;
        }
        
        // Показываем индикатор загрузки
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Обработка...';
    });
});
</script>
@endsection