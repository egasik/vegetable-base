document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('delivery-map');
    const form = document.getElementById('map-address-form');
    const hint = document.getElementById('map-hint');
    const checkButton = document.getElementById('map-check');

    if (!container || !form || !hint || !checkButton) {
        return;
    }

    if (!window.ymaps) {
        hint.textContent =
            'Карта не загрузилась. Проверьте ключ JavaScript API или воспользуйтесь поиском по адресу.';
        return;
    }

    ymaps.ready(() => {
        const coordinates = [
            Number(container.dataset.lat),
            Number(container.dataset.lon),
        ];

        const map = new ymaps.Map(container, {
            center: coordinates,
            zoom: 15,
            controls: ['zoomControl'],
        });

        const marker = new ymaps.Placemark(
            coordinates,
            {},
            { preset: 'islands#redDotIcon' }
        );

        map.geoObjects.add(marker);

        map.events.add('click', (event) => {
            const point = event.get('coords');

            marker.geometry.setCoordinates(point);

            form.elements.latitude.value = point[0];
            form.elements.longitude.value = point[1];

            checkButton.disabled = false;

            hint.textContent =
                'Нажмите «Проверить выбранный дом», затем подтвердите найденный адрес.';

            const confirmation = document.querySelector(
                '[name="address_confirmed"]'
            );

            if (confirmation) {
                confirmation.checked = false;
                confirmation.disabled = true;

                const submitButton =
                    confirmation.form.querySelector('button');

                if (submitButton) {
                    submitButton.disabled = true;
                }
            }
        });
    });
});
