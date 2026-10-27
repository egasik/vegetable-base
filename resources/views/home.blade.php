@extends('layouts.app')
@section('title', 'Овощи и фрукты для дома и бизнеса в Иркутске')
@section('description', 'Выбирайте овощи и фрукты в розницу от 0,5 кг или целыми упаковками. Соберите корзину и оформите доставку по Иркутску и Иркутской области.')
@section('content')
<div class="landing-home">
    @include('partials.home-garden')
    <section class="harvest-hero" aria-labelledby="hero-heading">
        <div class="hero-copy">
            <p class="eyebrow"><span class="availability-dot" aria-hidden="true"></span> Для дома, кафе и вашего дела</p>
            <h1 id="hero-heading">Хорошие продукты.<br>В вашей корзине.</h1>
            <p class="hero-description">Овощи и фрукты к семейному ужину или для целой кухни. Выбирайте нужный вес, добавляйте в корзину и оформляйте доставку.</p>
            <a class="button hero-cta" href="{{ route('cart.index') }}">Перейти в корзину <span aria-hidden="true">↗</span></a>
            @guest<p class="hero-caption">Для покупки войдите в аккаунт. Всё оформление на сайте.</p>@else<p class="hero-caption">Проверьте выбранные продукты перед оформлением.</p>@endguest
            <div class="hero-proof"><span>Розница от <strong>0,5 кг</strong></span><span>Опт <strong>упаковками</strong></span></div>
        </div>
        <div class="hero-visual" aria-label="Овощи и фрукты для вашей корзины">
            <div class="produce-orbit" aria-hidden="true"></div>
            <div class="produce-label label-top"><span class="small-label">Каждому свой объём</span><strong>Немного или с запасом</strong></div>
            <img src="{{ asset('images/harvest.svg') }}" width="500" height="440" alt="Корзина с помидором, зеленью и морковью" class="hero-harvest" fetchpriority="high">
            <div class="produce-label label-bottom"><span class="small-label">Без лишних подсчётов</span><strong>Цена за кг и упаковку</strong></div>
            <span class="visual-caption">Овощная база · Иркутск</span>
        </div>
    </section>
    <div class="service-strip" aria-label="Условия покупки">
        <p><strong>Нужный вес</strong><span>Розница с шагом 0,5 кг</span></p>
        <p><strong>Понятный опт</strong><span>Целыми упаковками</span></p>
        <p><strong>Доставка в ваш город</strong><span>Иркутск и Иркутская область</span></p>
    </div>
    <section class="landing-section" id="products" aria-labelledby="products-heading" data-reveal>
        <div class="section-heading"><div><p class="eyebrow">Начните с того, что любите</p><h2 id="products-heading">Что положим в корзину?</h2></div><a class="section-link" href="{{ route('catalog') }}">Весь каталог ↗</a></div>
        <div class="product-grid">@forelse($products as $product)@include('partials.product-card')@empty<div class="catalog-empty"><h3>Готовим для вас ассортимент</h3><p>Скоро здесь появятся продукты. Пока можно посмотреть, как устроена покупка.</p><a class="section-link" href="#how-it-works">Как оформить заказ ↓</a></div>@endforelse</div>
    </section>
    <section class="tagline-section" aria-label="Покупки в удобном объёме" data-tagline>
        <p class="eyebrow">Больше вкуса в обычном дне</p>
        <h2><span>Для</span> <span>ужина</span> <span>на</span> <span>двоих.</span><br><span>Для</span> <span>кухни</span> <span>на</span> <span>весь</span> <span>день.</span></h2>
        <p>Одна корзина для разных планов. Берите в розницу по весу или выбирайте упаковки для больших покупок.</p>
    </section>
    <section class="purchase-stories landing-section" aria-labelledby="purchase-heading" data-reveal>
        <div class="section-heading"><div><p class="eyebrow">Покупайте в своём ритме</p><h2 id="purchase-heading">Ровно столько, сколько нужно</h2></div></div>
        <div class="story-grid">
            <article class="story-card retail-story"><span class="story-number">01 / Для дома</span><h3>Для ужина.<br>И на всю неделю.</h3><p>От половины килограмма. Меняйте вес прямо в карточке товара и собирайте свой набор.</p><span class="story-chip">Шаг 0,5 кг</span></article>
            <article class="story-card wholesale-story"><span class="story-number">02 / Для бизнеса</span><h3>Когда готовите<br>не только для себя.</h3><p>Выбирайте целые упаковки. Их вес, стоимость и цена за килограмм видны до добавления в корзину.</p><span class="story-chip">Упаковки 10, 20 или 50 кг</span></article>
        </div>
    </section>
    <section class="landing-section how-section" id="how-it-works" aria-labelledby="how-heading" data-reveal>
        <div class="section-heading"><div><p class="eyebrow">От выбора до заказа</p><h2 id="how-heading">Три простых шага</h2></div></div>
        <ol class="steps-grid">
            <li><span class="step-number">01</span><h3>Выберите продукты</h3><p>Войдите в аккаунт, укажите вес или число упаковок и добавьте товары в корзину.</p></li>
            <li><span class="step-number">02</span><h3>Проверьте корзину</h3><p>Измените количество, проверьте сумму и подтвердите почту перед оформлением.</p></li>
            <li><span class="step-number">03</span><h3>Укажите адрес</h3><p>Найдите дом на карте, подтвердите адрес доставки и выберите оплату картой или СБП.</p></li>
        </ol>
        <p class="purchase-assurance">До создания заказа можно менять количество и удалять товары. Итоговая сумма видна в корзине.</p>
    </section>
    <section class="landing-section faq-section" aria-labelledby="faq-heading" data-reveal>
        <div><p class="eyebrow">Перед первой покупкой</p><h2 id="faq-heading">Всё, что хочется уточнить</h2><p>Простые ответы о весе, упаковках и оформлении.</p></div>
        <div class="faq-list">
            <details><summary>Можно купить меньше килограмма?</summary><p>Да. Для розничных товаров минимальный вес составляет 0,5 кг. Количество меняется с шагом 0,5 кг.</p></details>
            <details><summary>Чем опт отличается от розницы?</summary><p>В розницу вы выбираете вес в килограммах, оптом покупаете целые упаковки. Доступный вес упаковки указан в карточке товара.</p></details>
            <details><summary>Нужно ли регистрироваться?</summary><p>Каталог открыт всем. Чтобы добавлять товары в корзину, нужно войти. Перед оформлением подтвердите почту кодом из письма.</p></details>
            <details><summary>Куда вы доставляете?</summary><p>По Иркутску и Иркутской области. При оформлении система проверит город, улицу и дом. Квартиру, подъезд и комментарий укажите отдельно.</p></details>
            <details><summary>Как оплатить заказ?</summary><p>После создания заказа выберите банковскую карту или СБП. Оплата проходит через ЮKassa, а её результат отображается в заказе.</p></details>
            <details><summary>Где посмотреть свой заказ?</summary><p>В личном кабинете: там доступны состав, сумма и текущий статус ваших заказов.</p></details>
        </div>
    </section>
    <section class="final-basket" data-reveal><p class="eyebrow">Планы на вкусный день</p><h2>Пусть хорошее<br>начинается с корзины.</h2><p>Выбранные продукты, нужное количество и понятная сумма. Всё в одном месте.</p><a class="button" href="{{ route('cart.index') }}">Перейти в корзину ↗</a></section>
</div>
@endsection
