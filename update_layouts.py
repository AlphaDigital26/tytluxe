import glob
import os

pages_dir = r"e:\TYTluxe\tyt\tytluxe\resources\views\pages"
files = glob.glob(os.path.join(pages_dir, "*.blade.php"))

for filepath in files:
    with open(filepath, "r", encoding="utf-8") as f:
        content = f.read()
    
    new_content = content.replace("@extends('layouts.app')", "@extends('layouts.frontend')")
    
    with open(filepath, "w", encoding="utf-8") as f:
        f.write(new_content)

print(f"Updated {len(files)} layout extends.")
