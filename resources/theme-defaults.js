import plugin from 'tailwindcss/plugin';
import colors from 'tailwindcss/colors';
import fs from 'node:fs';
import { fileURLToPath } from 'node:url';

const readViews = (directory) => fs.readdirSync(directory, { withFileTypes: true })
    .map(entry => entry.isDirectory() ? readViews(`${directory}/${entry.name}`) : fs.readFileSync(`${directory}/${entry.name}`, 'utf8')).join('\n');
const viewClasses = readViews(fileURLToPath(new URL('./views', import.meta.url)));

// Existing views share Tailwind palettes. Supply dark defaults centrally while
// allowing explicit dark: utilities (emitted later) to override these defaults.
export default plugin(({ addComponents, e }) => {
    const rules = {};
    const add = (utility, property, value) => {
        for (const state of ['', 'hover', 'focus', 'active', 'disabled']) {
            const name = state ? `${state}:${utility}` : utility;
            // Opacity and state variants are emitted only when a view uses them.
            if ((state || utility.includes('/')) && !viewClasses.includes(name)) continue;
            rules[`.dark .${e(name)}${state ? `:${state}` : ''}`] = { [property]: value };
        }
    };
    add('bg-white', 'backgroundColor', '#111827');
    for (const name of ['slate', 'gray', 'zinc', 'neutral', 'stone', 'blue', 'sky', 'cyan', 'teal', 'emerald', 'green', 'red', 'rose', 'orange', 'amber', 'yellow', 'violet', 'purple', 'indigo', 'pink', 'fuchsia']) {
        const palette = colors[name];
        const neutral = ['slate', 'gray', 'zinc', 'neutral', 'stone'].includes(name);
        for (const shade of [50, 100, 200]) {
            add(`bg-${name}-${shade}`, 'backgroundColor', neutral ? colors.slate[shade === 200 ? 700 : 800] : `color-mix(in srgb, ${palette[900]} 65%, #111827)`);
            for (const opacity of [10, 20, 30, 40, 50, 60, 70, 75, 80, 90, 95]) {
                add(`bg-${name}-${shade}/${opacity}`, 'backgroundColor', `color-mix(in srgb, ${neutral ? colors.slate[800] : palette[900]} ${opacity}%, transparent)`);
            }
            add(`border-${name}-${shade}`, 'borderColor', neutral ? colors.slate[700] : palette[800]);
            rules[`.dark .${e(`divide-${name}-${shade}`)} > :not([hidden]) ~ :not([hidden])`] = { borderColor: colors.slate[700] };
        }
        add(`border-${name}-300`, 'borderColor', palette[600]);
        for (const shade of [500, 600, 700, 800, 900, 950]) {
            add(`text-${name}-${shade}`, 'color', neutral ? colors.slate[shade >= 800 ? 100 : 300] : palette[shade >= 800 ? 200 : 300]);
        }
    }
    add('text-black', 'color', '#f1f5f9');
    add('bg-custom', 'backgroundColor', '#0f172a');
    add('bg-secondary', 'backgroundColor', '#1e293b');
    addComponents(rules);
});
