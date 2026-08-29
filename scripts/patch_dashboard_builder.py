from pathlib import Path
import re

path = Path('modules/Admin/views/dashboard.php')
text = path.read_text(encoding='utf-8', errors='replace')
original = text

# Add initialSelected select call in initFormBuilder
init_pattern = re.compile(r'(function initFormBuilder\(\) \{\n  if \(window\.formBuilderInitialized\) return;\n  window\.formBuilderInitialized = true;\n)')
text, init_count = init_pattern.subn(r"\1  const initialSelected = document.querySelector('.canvas-element.selected') || document.querySelector('.canvas-element');\n  if (initialSelected) selectCanvasElement(initialSelected);\n", text, count=1)

# Patch initial canvas element markup for data-type and control handlers
element_types = {
    'elem1': 'text',
    'elem2': 'email',
    'elem3': 'textarea',
    'elem4': 'select',
    'elem5': 'number',
    'elem6': 'file',
}
for elem_id, data_type in element_types.items():
    # add data-type attribute if missing
    text = re.sub(
        rf'(<div class="canvas-element(?: selected)?" id="{elem_id}")(?![^>]*data-type)(>)',
        rf'\1 data-type="{data_type}"\2',
        text,
    )
    # add edit/remove onclick handlers within controls
    block_re = re.compile(
        rf'(<div class="canvas-element(?: selected)?" id="{elem_id}"[\s\S]*?<div class="elem-controls">)([\s\S]*?)(</div>\s*</div>)',
        re.MULTILINE,
    )
    m = block_re.search(text)
    if m:
        controls = m.group(2)
        updated = controls
        updated = re.sub(
            r'<button class="elem-btn" style="([^"]*)"><i class="fas fa-edit"></i></button>',
            r'<button class="elem-btn" style="\1" onclick="editCanvasElement(event)"><i class="fas fa-edit"></i></button>',
            updated,
        )
        updated = re.sub(
            r'<button class="elem-btn" style="([^"]*)"><i class="fas fa-times"></i></button>',
            r'<button class="elem-btn" style="\1" onclick="removeCanvasElement(event)"><i class="fas fa-times"></i></button>',
            updated,
        )
        if updated != controls:
            text = text[:m.start(2)] + updated + text[m.end(2):]

if text != original:
    path.write_text(text, encoding='utf-8')
    print('dashboard.php patched')
    print('initFormBuilder patch count:', init_count)
else:
    print('dashboard.php already up to date')
