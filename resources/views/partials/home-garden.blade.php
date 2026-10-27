{{-- Decorative illustrations are intentionally excluded from the accessibility tree. --}}
<div class="home-garden" aria-hidden="true">
    <svg class="garden-symbols" xmlns="http://www.w3.org/2000/svg"><defs>
        <symbol id="garden-carrot" viewBox="0 0 120 160"><path d="M38 53Q60 39 83 54Q81 88 48 142Q30 111 30 75Q30 60 38 53Z"/><path d="M53 47Q27 18 38 9Q53 15 58 43M60 44Q58 8 73 5Q84 20 67 45M70 46Q93 17 102 30Q98 44 76 51M34 78L50 81M42 103L58 106M66 66L81 70M55 120L62 122"/></symbol>
        <symbol id="garden-tomato" viewBox="0 0 150 140"><path d="M68 43C35 20 5 52 15 92C23 130 65 137 82 126C110 135 142 107 136 72C132 43 106 27 83 42"/><path d="M74 47L51 26L72 32L81 12L87 33L111 25L94 47L106 59L82 53L63 62ZM82 30Q84 17 94 10M29 78Q28 65 40 57"/></symbol>
        <symbol id="garden-onion" viewBox="0 0 130 160"><path d="M57 42C49 66 17 77 18 108C19 149 108 150 112 110C114 81 82 65 78 43ZM58 43Q39 23 43 7M67 43L67 7M76 43Q89 22 87 9M50 65Q20 119 59 135M70 58Q48 109 72 136M83 69Q112 118 88 131M56 143L49 152M67 145L67 155M77 143L83 152"/></symbol>
        <symbol id="garden-leaf" viewBox="0 0 140 160"><path d="M28 135C0 80 48 15 121 12C135 80 102 137 28 135ZM18 151L103 32M37 124L20 101M51 103L29 75M67 82L48 47M78 65L112 68M60 91L106 98M41 118L86 122"/></symbol>
        <symbol id="garden-pepper" viewBox="0 0 130 160"><path d="M64 41C42 19 17 42 18 81C18 117 29 151 49 139C62 153 78 150 87 137C111 144 120 114 116 79C113 42 94 26 73 41M65 39Q61 17 81 7L88 14Q74 22 74 39M62 55Q45 81 50 126M81 56Q95 80 86 123"/></symbol>
        <symbol id="garden-radish" viewBox="0 0 130 160"><path d="M41 61C17 82 26 120 56 134L66 153L71 134C106 120 115 83 92 63C79 52 54 51 41 61ZM60 55Q25 28 40 8Q58 13 65 51M71 51Q70 13 91 8Q106 35 78 55M39 83Q48 76 52 75M30 106Q67 119 105 105"/></symbol>
        <symbol id="garden-potato" viewBox="0 0 160 120"><path d="M31 30C1 43 13 90 43 101C63 122 118 112 140 86C162 57 144 27 115 22C88 4 52 10 31 30ZM39 49L44 53M70 34L73 39M107 45L111 42M123 74L119 78M86 91L83 87M54 78L57 74"/></symbol>
        <symbol id="garden-broccoli" viewBox="0 0 150 160"><path d="M62 100L54 148L95 148L85 98M64 117L46 94M82 119L104 94M36 100C8 99 7 68 24 58C10 33 42 16 59 28C71 0 108 12 111 32C140 23 155 58 131 75C145 99 112 117 95 100C83 115 53 113 48 98Z"/></symbol>
        <symbol id="garden-cucumber" viewBox="0 0 100 170"><path d="M65 11C94 15 77 81 63 119C51 154 34 170 15 153C-2 139 18 106 29 71C39 42 39 9 65 11ZM63 25Q64 76 39 132M43 49L47 53M59 69L64 71M30 89L34 93M44 109L49 110M25 134L29 137"/></symbol>
    </defs></svg>
    @php
        $garden = [];
        $vegetables = ['carrot', 'leaf', 'tomato', 'onion', 'radish', 'cucumber', 'potato', 'pepper', 'broccoli'];
        $sizes = [144, 160, 128, 176];
        $angles = [-18, 12, 24, -12, 18, -24];
        for ($row = 0; $row < 28; $row++) {
            foreach (['left', 'right'] as $side => $name) {
                $index = $row * 2 + $side;
                $garden[] = [$vegetables[$index % count($vegetables)], $name,
                    round(1 + $row * 3.5 + $side * 1.3, 1), $sizes[$index % 4], $angles[$index % 6],
                    .32 + ($index % 5) * .07, $row % 2 === 0 ? 0 : -48];
            }
        }
    @endphp
    @foreach($garden as $motif)
        <span class="garden-motif garden-motif-{{ $motif[1] }} garden-color-{{ $loop->index % 4 }} {{ $loop->index % 5 === 0 ? 'garden-mobile' : '' }}" style="--garden-top:{{ $motif[2] }}%;--garden-size:{{ $motif[3] }}px;--garden-angle:{{ $motif[4] }}deg;--garden-lane:{{ $motif[6] }}px" data-garden-speed="{{ $motif[5] }}"><svg viewBox="0 0 {{ $motif[0] === 'potato' ? '160 120' : '150 170' }}" focusable="false"><use href="#garden-{{ $motif[0] }}" width="100%" height="100%"/></svg></span>
    @endforeach
</div>
