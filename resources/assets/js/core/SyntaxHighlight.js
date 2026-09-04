/**
 * SyntaxHighlight — lightweight token-based syntax highlighter.
 *
 * Usage:
 *   import { highlight } from '../core/SyntaxHighlight.js';
 *   el.innerHTML = highlight(code, 'js');
 */

const HTML_ESC = { '&': '&amp;', '<': '&lt;', '>': '&gt;' };

function esc(s) {
    return s.replace(/[&<>]/g, c => HTML_ESC[c]);
}

function wrap(cls, text) {
    return `<span class="sh-${cls}">${text}</span>`;
}

/* ── Language definitions ──────────────────────────────── */

const LANG = {

    js: {
        keywords: /\b(const|let|var|function|return|if|else|for|while|do|switch|case|break|continue|new|delete|typeof|instanceof|in|of|class|extends|super|import|from|export|default|try|catch|finally|throw|async|await|yield|this|null|undefined|true|false|void|static|get|set)\b/,
        types: /\b(Array|Boolean|Date|Error|Function|Map|Math|Number|Object|Promise|Proxy|RegExp|Set|String|Symbol|WeakMap|WeakSet|console|window|document|parseInt|parseFloat|isNaN|isFinite|setTimeout|setInterval|clearTimeout|clearInterval|fetch|JSON|Promise|Error)\b/,
        strings: [
            /`(?:[^`\\]|\\.)*`/gs,
            /"(?:[^"\\]|\\.)*"/g,
            /'(?:[^'\\]|\\.)*'/g,
        ],
        comments: [
            /\/\/.*$/gm,
            /\/\*[\s\S]*?\*\//g,
        ],
        numbers: /\b\d+\.?\d*(?:[eE][+-]?\d+)?\b/g,
        operators: /[!=]==?|[!=]==?=?|&&|\|\||[+\-*/%]=?|[<>]=?|\?\?|\?\.|\.{3}/g,
        regex: /\/(?![*/])(?:[^/\\]|\\.)+\/[gimsuy]*/g,
    },

    ts: null, // alias to js

    php: {
        keywords: /\b(function|return|if|else|elseif|for|foreach|while|do|switch|case|break|continue|new|clone|instanceof|class|extends|implements|interface|trait|abstract|final|static|public|private|protected|const|var|let|yield|fn|match|enum|readonly|never|void|null|true|false|self|parent|print_r|echo|die|exit|array|list|extract|compact|isset|empty|unset)\b/,
        types: /\b(int|float|string|bool|array|callable|object|mixed|self|parent|null|void|never|false|true)\b/,
        strings: [
            /"(?:[^"\\]|\\.)*"/g,
            /'(?:[^'\\]|\\.)*'/g,
        ],
        comments: [
            /\/\/.*$/gm,
            /\/\*[\s\S]*?\*\//g,
            /#.*$/gm,
        ],
        numbers: /\b\d+\.?\d*(?:[eE][+-]?\d+)?\b/g,
        phpTag: /<\?php|<\?=|<\?/g,
    },

    css: {
        keywords: /\b(import|charset|media|keyframes|font-face|supports|layer|property)\b/,
        selectors: /([.#][\w-]+|[\w-]+(?=\s*\{)|[\w-]+(?=\s*[,>:~+]))/g,
        strings: [
            /"(?:[^"\\]|\\.)*"/g,
            /'(?:[^'\\]|\\.)*'/g,
        ],
        comments: [
            /\/\*[\s\S]*?\*\//g,
        ],
        properties: /[\w-]+(?=\s*:)/g,
        numbers: /#[0-9a-fA-F]{3,8}\b|\b\d+\.?\d*(?:px|em|rem|%|vh|vw|deg|s|ms|fr)?\b/g,
        atRules: /@[\w-]+/g,
    },

    html: {
        tags: /<(\/?)([\w-]+)([^>]*?)(\/?)>/g,
        attributes: /\b([\w-]+)(?==)/g,
        strings: [
            /"(?:[^"\\]|\\.)*"/g,
            /'(?:[^'\\]|\\.)*'/g,
        ],
        comments: [
            /<!--[\s\S]*?-->/g,
        ],
        doctype: /<!DOCTYPE[^>]*>/gi,
    },

    xml: null, // alias to html

    python: {
        keywords: /\b(def|class|return|if|elif|else|for|while|break|continue|pass|import|from|as|try|except|finally|raise|with|yield|lambda|global|nonlocal|assert|del|in|not|and|or|is|True|False|None|async|await|print|self|cls)\b/,
        types: /\b(int|float|str|bool|list|dict|tuple|set|bytes|type|object|None|True|False)\b/,
        strings: [
            /"""[\s\S]*?"""/g,
            /'''[\s\S]*?'''/g,
            /"(?:[^"\\]|\\.)*"/g,
            /'(?:[^'\\]|\\.)*'/g,
        ],
        comments: [
            /#.*$/gm,
        ],
        numbers: /\b\d+\.?\d*(?:[eE][+-]?\d+)?\b/g,
        decorators: /@\w+/g,
    },

    java: {
        keywords: /\b(public|private|protected|static|final|abstract|class|interface|extends|implements|new|return|if|else|for|while|do|switch|case|break|continue|try|catch|finally|throw|throws|instanceof|void|null|true|false|this|super|import|package|enum|synchronized|volatile|transient|native|strictfp|assert|yield|record|sealed|permits|var|final)\b/,
        types: /\b(int|long|short|byte|float|double|char|boolean|String|Integer|Boolean|Long|Double|Float|Character|Object|System|List|Map|Set|ArrayList|HashMap|HashSet)\b/,
        strings: [
            /"(?:[^"\\]|\\.)*"/g,
        ],
        comments: [
            /\/\/.*$/gm,
            /\/\*[\s\S]*?\*\//g,
        ],
        numbers: /\b\d+\.?\d*[lLfFdD]?\b/g,
    },

    c: {
        keywords: /\b(auto|break|case|char|const|continue|default|do|double|else|enum|extern|float|for|goto|if|inline|int|long|register|restrict|return|short|signed|sizeof|static|struct|switch|typedef|union|unsigned|void|volatile|while|_Bool|_Complex|_Imaginary)\b/,
        types: /\b(size_t|ssize_t|int8_t|int16_t|int32_t|int64_t|uint8_t|uint16_t|uint32_t|uint64_t|bool|nullptr|NULL|TRUE|FALSE|printf|scanf|malloc|free|calloc|realloc|memcpy|memset|strlen|strcpy|strcat|strcmp)\b/,
        strings: [
            /"(?:[^"\\]|\\.)*"/g,
            /'(?:[^'\\]|\\.){1}'/g,
        ],
        comments: [
            /\/\/.*$/gm,
            /\/\*[\s\S]*?\*\//g,
        ],
        numbers: /\b0[xX][0-9a-fA-F]+(?:[uUlL]*)?\b|\b\d+\.?\d*(?:[eE][+-]?\d+)?(?:[fFlL]*)?\b/g,
        preprocessor: /^\s*#.*$/gm,
    },

    cpp: null, // alias to c

    cs: {
        keywords: /\b(abstract|as|base|bool|break|byte|case|catch|char|checked|class|const|continue|decimal|default|delegate|do|double|else|enum|event|explicit|extern|false|finally|fixed|float|for|foreach|goto|if|implicit|in|int|interface|internal|is|lock|long|namespace|new|null|object|operator|out|override|params|private|protected|public|readonly|ref|return|sbyte|sealed|short|sizeof|stackalloc|string|struct|switch|this|throw|true|try|typeof|uint|ulong|unchecked|unsafe|ushort|using|var|virtual|void|volatile|while|yield|async|await|when|where|get|set|add|remove|init|record|with|not)\b/,
        types: /\b(Int16|Int32|Int64|UInt16|UInt32|UInt64|Single|Double|Boolean|Char|String|Byte|SByte|Decimal|Object|DateTime|TimeSpan|Guid|Array|List|Dictionary|IEnumerable|Task|ValueTuple)\b/,
        strings: [
            /"(?:[^"\\]|\\.)*"/g,
            /\$"(?:[^"\\]|\\.)*"/g,
            /@"(?:""|[^"])*"/g,
        ],
        comments: [
            /\/\/.*$/gm,
            /\/\*[\s\S]*?\*\//g,
        ],
        numbers: /\b\d+\.?\d*(?:[eE][+-]?\d+)?[fFdDmM]?\b/g,
    },

    rb: {
        keywords: /\b(def|class|module|return|if|elsif|else|unless|for|while|until|do|break|next|redo|retry|raise|begin|rescue|ensure|end|yield|lambda|proc|self|nil|true|false|and|or|not|in|is_a?|respond_to?|include|extend|require|require_relative|attr_reader|attr_writer|attr_accessor|super|alias|undef|defined\?|gsub|scan|match|send|public_send|instance_methods|method|freeze|dup|clone|tap|then|yield_self|filter_map|collect|map|select|reject|reduce|inject|each_with_index|each_with_object|with_index|with_object|group_by|partition|sort_by|sort|uniq|compact|flatten|reverse|rotate|shuffle|min|max|sum|count|size|length|empty\?|any\?|all\?|none\?|one\?|include\?|member\?|each|each_with_index|each_with_object|each_with_key|each_pair|map_with_index|flat_map|each_cons|each_slice|chunk|slice_when|slice_before|slice_after|slice_when|cons|prev_values|next_values|zip|transpose|product|combination|permutation|repeated_combination|repeated_permutation|循环)\b/,
        types: /\b(String|Integer|Float|Array|Hash|Symbol|Regexp|NilClass|TrueClass|FalseClass|Numeric|Comparable|Enumerable|Kernel|Object|BasicObject)\b/,
        strings: [
            /"(?:[^"\\]|\\.)*"/g,
            /'(?:[^'\\]|\\.)*'/g,
        ],
        comments: [
            /#.*$/gm,
        ],
        numbers: /\b\d+\.?\d*(?:[eE][+-]?\d+)?\b/g,
        symbols: /:[\w]+/g,
    },

    go: {
        keywords: /\b(func|return|if|else|for|range|switch|case|default|break|continue|go|defer|select|chan|map|struct|interface|package|import|type|var|const|new|make|len|cap|append|copy|delete|close|panic|recover|error|bool|byte|rune|string|int|int8|int16|int32|int64|uint|uint8|uint16|uint32|uint64|uintptr|float32|float64|complex64|complex128|true|false|iota|nil)\b/,
        types: /\b(fmt|strings|strconv|math|errors|sync|context|io|os|net|http|json|time|log|sort|regexp|path|filepath|crypto|encoding|reflect|testing)\b/,
        strings: [
            /`[^`]*`/g,
            /"(?:[^"\\]|\\.)*"/g,
        ],
        comments: [
            /\/\/.*$/gm,
            /\/\*[\s\S]*?\*\//g,
        ],
        numbers: /\b\d+\.?\d*(?:[eE][+-]?\d+)?\b/g,
    },

    rs: {
        keywords: /\b(fn|let|mut|const|struct|enum|impl|trait|pub|use|mod|crate|self|super|return|if|else|for|while|loop|break|continue|match|as|in|ref|move|async|await|dyn|where|type|static|unsafe|extern|crate|true|false|Some|None|Ok|Err|Self)\b/,
        types: /\b(i8|i16|i32|i64|i128|u8|u16|u32|u64|u128|f32|f64|bool|char|str|String|Vec|Box|Rc|Arc|HashMap|HashSet|Option|Result|String|usize|isize)\b/,
        strings: [
            /"(?:[^"\\]|\\.)*"/g,
        ],
        comments: [
            /\/\/.*$/gm,
            /\/\*[\s\S]*?\*\//g,
        ],
        numbers: /\b\d+\.?\d*(?:_?\d+)*(?:[eE][+-]?\d+)?(?:f32|f64|i8|i16|i32|i64|i128|u8|u16|u32|u64|u128|usize|isize)?\b/g,
    },

    sh: {
        keywords: /\b(if|then|else|elif|fi|for|while|do|done|case|esac|function|return|exit|local|export|source|echo|printf|read|test|exec|trap|shift|set|unset|true|false|in)\b/,
        strings: [
            /"(?:[^"\\]|\\.)*"/g,
            /'(?:[^'\\])*'/g,
        ],
        comments: [
            /#.*$/gm,
        ],
        variables: /\$\{?[\w]+\}?|\$[\w]+/g,
        numbers: /\b\d+\b/g,
    },

    json: {
        strings: [
            /"(?:[^"\\]|\\.)*"(?=\s*:)/g,
            /"(?:[^"\\]|\\.)*"/g,
        ],
        numbers: /-?\b\d+\.?\d*(?:[eE][+-]?\d+)?\b/g,
        keywords: /\b(true|false|null)\b/g,
        punctuation: /[{}\[\]:,]/g,
    },

    sql: {
        keywords: /\b(SELECT|FROM|WHERE|INSERT|INTO|VALUES|UPDATE|SET|DELETE|CREATE|DROP|ALTER|TABLE|INDEX|VIEW|DATABASE|SCHEMA|JOIN|LEFT|RIGHT|INNER|OUTER|ON|AND|OR|NOT|IN|EXISTS|BETWEEN|LIKE|IS|NULL|AS|ORDER|BY|GROUP|HAVING|LIMIT|OFFSET|UNION|ALL|DISTINCT|COUNT|SUM|AVG|MIN|MAX|CASE|WHEN|THEN|ELSE|END|BEGIN|COMMIT|ROLLBACK|GRANT|REVOKE|PRIMARY|KEY|FOREIGN|REFERENCES|CONSTRAINT|UNIQUE|CHECK|DEFAULT|AUTO_INCREMENT|IF|REPLACE|TRIGGER|PROCEDURE|FUNCTION|RETURNS|DECLARE|SET|EXEC|EXECUTE|OPEN|CLOSE|FETCH|NEXT|PRIOR|FIRST|LAST|ABSOLUTE|RELATIVE|ROWCOUNT|CURSOR|WITH|RECURSIVE|PIVOT|UNPIVOT|MERGE|USING|MATCHED|NOT|OUTPUT|EXCEPT|INTERSECT|TOP|PERCENT|TIES|OFFSET|FETCH|NEXT|ROWS|ONLY|WINDOW|OVER|PARTITION|ROW_NUMBER|RANK|DENSE_RANK|NTILE|LAG|LEAD|FIRST_VALUE|LAST_VALUE|NTH_VALUE|STRING_AGG|ARRAY_AGG|JSON_AGG|JSONB_AGG|JSON_BUILD_OBJECT|JSONB_BUILD_OBJECT|JSON_AGG|JSONB_AGG|JSON_BUILD_OBJECT|JSONB_BUILD_OBJECT)\b/i,
        strings: [
            /'(?:[^'\\]|\\.)*'/g,
        ],
        comments: [
            /--.*$/gm,
            /\/\*[\s\S]*?\*\//g,
        ],
        numbers: /\b\d+\.?\d*\b/g,
    },

    md: {
        headings: /^(#{1,6}\s.*)$/gm,
        bold: /\*\*(?:[^*]|\*(?!\*))*\*\*/g,
        italic: /(?<!\*)\*(?!\*)(?:[^*])*\*(?!\*)/g,
        code: /`[^`]+`/g,
        codeBlock: /^```[\s\S]*?^```/gm,
        links: /\[([^\]]+)\]\(([^)]+)\)/g,
        lists: /^\s*[-*+]\s/gm,
        numbers: /^\s*\d+\.\s/gm,
    },
};

LANG.ts = LANG.js;
LANG.cpp = LANG.c;
LANG.xml = LANG.html;

/* ── Highlighting engine ──────────────────────────────── */

function tokenize(code, lang) {
    const def = LANG[lang];
    if (!def) return esc(code);

    const tokens = [];

    function addTokens(patterns, cls) {
        if (!patterns) return;
        const arr = Array.isArray(patterns) ? patterns : [patterns];
        for (const re of arr) {
            const rx = new RegExp(re.source, re.flags);
            let m;
            while ((m = rx.exec(code)) !== null) {
                tokens.push({ start: m.index, end: m.index + m[0].length, cls, text: m[0] });
            }
        }
    }

    addTokens(def.comments, 'comment');
    addTokens(def.strings, 'string');
    addTokens(def.phpTag, 'php-tag');
    addTokens(def.preprocessor, 'preprocessor');
    addTokens(def.headings, 'heading');
    addTokens(def.bold, 'bold');
    addTokens(def.italic, 'italic');
    addTokens(def.codeBlock, 'code-block');
    addTokens(def.code, 'inline-code');
    addTokens(def.links, 'link');
    addTokens(def.keywords, 'keyword');
    addTokens(def.types, 'type');
    addTokens(def.numbers, 'number');
    addTokens(def.operators, 'operator');
    addTokens(def.regex, 'regex');
    addTokens(def.properties, 'property');
    addTokens(def.atRules, 'at-rule');
    addTokens(def.selectors, 'selector');
    addTokens(def.decorators, 'decorator');
    addTokens(def.variables, 'variable');
    addTokens(def.punctuation, 'punctuation');
    addTokens(def.symbols, 'symbol');
    addTokens(def.tags, 'tag');
    addTokens(def.attributes, 'attribute');

    tokens.sort((a, b) => a.start - b.start || b.end - a.end);

    const merged = [];
    let lastEnd = 0;
    for (const t of tokens) {
        if (t.start < lastEnd) continue;
        if (t.start > lastEnd) {
            merged.push(esc(code.slice(lastEnd, t.start)));
        }
        merged.push(wrap(t.cls, esc(t.text)));
        lastEnd = t.end;
    }
    if (lastEnd < code.length) {
        merged.push(esc(code.slice(lastEnd)));
    }

    return merged.join('');
}

/* ── HTML post-processing ─────────────────────────────── */

function processHtml(code, def) {
    let result = code;

    if (def.tags) {
        const rx = new RegExp(def.tags.source, def.tags.flags);
        result = result.replace(rx, (full, slash, tag, attrs, selfClose) => {
            const highlightedAttrs = attrs.replace(
                /\b([\w-]+)(?==)/g,
                (_, name) => wrap('attribute', esc(name))
            ).replace(
                /"(?:[^"\\]|\\.)*"/g,
                (s) => wrap('string', esc(s))
            ).replace(
                /'(?:[^'\\]|\\.)*'/g,
                (s) => wrap('string', esc(s))
            );
            return `${wrap('punctuation', esc('<'))}${slash ? wrap('punctuation', esc('/')) : ''}${wrap('tag', esc(tag))}${highlightedAttrs}${selfClose ? wrap('punctuation', esc('/')) : ''}${wrap('punctuation', esc('>'))}`;
        });
    }

    if (def.doctype) {
        result = result.replace(
            new RegExp(def.doctype.source, def.doctype.flags),
            m => wrap('doctype', esc(m))
        );
    }

    return result;
}

/* ── Public API ───────────────────────────────────────── */

const LANG_MAP = {
    js: 'js', javascript: 'js', mjs: 'js', cjs: 'js',
    ts: 'ts', typescript: 'ts', jsx: 'js', tsx: 'ts',
    php: 'php',
    css: 'css', scss: 'css', less: 'css',
    html: 'html', htm: 'html', xml: 'xml',
    py: 'python', python: 'python',
    java: 'java',
    c: 'c', h: 'c',
    cpp: 'cpp', cxx: 'cpp', cc: 'cpp', hpp: 'cpp',
    cs: 'cs', 'csharp': 'cs',
    rb: 'ruby', ruby: 'rb',
    go: 'go', golang: 'go',
    rs: 'rust', rust: 'rs',
    sh: 'sh', bash: 'sh', zsh: 'sh', fish: 'sh',
    json: 'json',
    sql: 'sql',
    md: 'md', markdown: 'md',
    yaml: 'sh', yml: 'sh',
    txt: 'text', log: 'text', csv: 'text',
    ini: 'sh', cfg: 'sh', conf: 'sh', env: 'sh',
};

export function highlight(code, lang) {
    const mapped = LANG_MAP[lang] || lang;
    const def = LANG[mapped];

    if (!def) return esc(code);

    let highlighted = tokenize(code, def);

    if (mapped === 'html' || mapped === 'xml') {
        highlighted = processHtml(code, def);
    }

    return highlighted;
}

export function getLangFromExt(ext) {
    return LANG_MAP[ext] || ext;
}
