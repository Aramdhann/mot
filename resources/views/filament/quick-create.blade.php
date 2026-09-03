@php
    $items = [
        ['label' => 'Transaction', 'url' => route('filament.admin.resources.transactions.index', ['action' => 'create'])],
        ['label' => 'Wallet', 'url' => route('filament.admin.resources.wallets.index', ['action' => 'create'])],
        ['label' => 'Budget', 'url' => route('filament.admin.resources.budgets.index', ['action' => 'create'])],
        ['label' => 'Loan', 'url' => route('filament.admin.resources.loans.index', ['action' => 'create'])],
        ['label' => 'Note', 'url' => route('filament.admin.resources.notes.index', ['action' => 'create'])],
        ['label' => 'Todo', 'url' => route('filament.admin.resources.todo-cards.index', ['action' => 'create'])],
    ];
@endphp

<style>
    .mot-fab { position: fixed; bottom: 20px; right: 20px; z-index: 50; display: flex; flex-direction: column; align-items: flex-end; gap: 8px; }
    .mot-fab-menu { width: 190px; border-radius: 12px; border: 1px solid #e7e5e4; background: #fff; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.15); overflow: hidden; display: none; }
    .mot-fab-menu.mot-open { display: block; }
    .mot-fab-menu a { display: block; padding: 10px 16px; font-size: 14px; font-weight: 500; color: #44403c; text-decoration: none; }
    .mot-fab-menu a:hover { background: #fffbeb; }
    .mot-fab-btn { width: 56px; height: 56px; border-radius: 9999px; background: #f59e0b; color: #1c1917; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.2); transition: background .15s, transform .2s; }
    .mot-fab-btn:hover { background: #fbbf24; }
    .mot-fab-btn svg { width: 28px; height: 28px; transition: transform .2s; }
    .mot-fab-btn.mot-open svg { transform: rotate(45deg); }
    html.dark .mot-fab-menu { background: #292524; border-color: #44403c; }
    html.dark .mot-fab-menu a { color: #e7e5e4; }
    html.dark .mot-fab-menu a:hover { background: #44403c; }
</style>

<div class="mot-fab" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <div class="mot-fab-menu" :class="open ? 'mot-open' : ''">
        @foreach ($items as $item)
            <a href="{{ $item['url'] }}">{{ '+ '.$item['label'] }}</a>
        @endforeach
    </div>

    <button class="mot-fab-btn" :class="open ? 'mot-open' : ''" @click="open = !open" :aria-expanded="open" aria-label="Quick create">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
        </svg>
    </button>
</div>
