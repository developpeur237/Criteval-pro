from pathlib import Path
import re

path = Path('modules/Admin/views/dashboard.php')
text = path.read_text(encoding='utf-8', errors='replace')
old = (
    'function initFormBuilder() {\n'
    '  if (window.formBuilderInitialized) return;\n'
    '  window.formBuilderInitialized = true;\n'
    '  const initialSelected = document.querySelector(\'.canvas-element.selected\') || document.querySelector(\'.canvas-element\');\n'
    '  if (initialSelected) selectCanvasElement(initialSelected);\n'
    '  setupBuilderDragDrop();\n'
    '  bindCanvasElementSelection();\n'
    '  bindBuilderPropertyUpdates();\n'
    '  initTinyMCEEditor();\n'
    '  initCanvasSorter();\n'
    '}'
)
new = (
    'function initFormBuilder() {\n'
    '  if (window.formBuilderInitialized) return;\n'
    '  window.formBuilderInitialized = true;\n'
    '  setupBuilderDragDrop();\n'
    '  bindCanvasElementSelection();\n'
    '  bindBuilderPropertyUpdates();\n'
    '  initTinyMCEEditor();\n'
    '  const initialSelected = document.querySelector(\'.canvas-element.selected\') || document.querySelector(\'.canvas-element\');\n'
    '  if (initialSelected) selectCanvasElement(initialSelected);\n'
    '  initCanvasSorter();\n'
    '}'
)
if old not in text:
    raise SystemExit('Expected initFormBuilder block not found')
text = text.replace(old, new, 1)
path.write_text(text, encoding='utf-8')
print('dashboard.php initFormBuilder order fixed')
