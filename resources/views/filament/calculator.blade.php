<div x-data="{
        expr: '',
        get result() {
            if (! this.expr || ! /[+\-*/]/.test(this.expr)) return null;
            if (! /^[\d+\-*/(). ]+$/.test(this.expr)) return null;
            try {
                const r = new Function('return (' + this.expr.replace(/\s/g, '') + ')')();
                return Number.isFinite(r) ? r : null;
            } catch (e) { return null; }
        },
        press(k) { this.expr += k; },
        use() {
            if (this.result === null) return;
            $wire.set(@js($statePath), String(this.result)).then(() => $wire.unmountAction());
        },
    }" style="width:100%;max-width:300px;">
    <div style="border:1px solid #e7e5e4;border-radius:10px;padding:12px;margin-bottom:12px;text-align:right;">
        <div x-text="expr || '0'"
            style="font-family:ui-monospace,monospace;font-size:18px;color:#1c1917;word-break:break-all;min-height:24px;">
        </div>
        <div x-text="result !== null ? '= ' + Number(result.toFixed(2)) : ''"
            style="font-size:14px;font-weight:700;color:#f59e0b;min-height:20px;"></div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">
        <template x-for="key in ['7','8','9','/','4','5','6','*','1','2','3','-','0','.','(',')','+']" :key="key">
            <button type="button" x-text="key" @click="press(key)"
                style="padding:14px 0;border:1px solid #e7e5e4;border-radius:10px;background:#fff;font-size:16px;font-weight:600;color:#1c1917;cursor:pointer;">
            </button>
        </template>
        <button type="button" @click="expr = ''"
            style="padding:14px 0;border:1px solid #e7e5e4;border-radius:10px;background:#fff;font-size:14px;font-weight:600;color:#dc2626;cursor:pointer;">C</button>
        <button type="button" @click="expr = expr.slice(0, -1)"
            style="padding:14px 0;border:1px solid #e7e5e4;border-radius:10px;background:#fff;font-size:14px;font-weight:600;color:#78716c;cursor:pointer;">&#9003;</button>
        <button type="button" @click="use()" :disabled="result === null"
            style="padding:14px 0;border:1px solid #f59e0b;border-radius:10px;background:#f59e0b;font-size:14px;font-weight:700;color:#1c1917;cursor:pointer;transition:opacity .15s;"
            :style="result === null ? 'opacity:.4;cursor:not-allowed' : ''">Use</button>
    </div>
</div>

<style>
    html.dark div[style*="border:1px solid #e7e5e4"] {
        border-color: #44403c !important;
    }

    html.dark button[style*="background:#fff"] {
        background: #292524 !important;
        color: #fafaf9 !important;
    }

    html.dark div[style*="color:#1c1917"] {
        color: #fafaf9 !important;
    }
</style>