import os
import glob

models_dir = 'app/Models'
for filepath in glob.glob(os.path.join(models_dir, '*.php')):
    with open(filepath, 'r') as f:
        content = f.read()
    
    if 'protected  = [];' in content:
        new_content = content.replace('protected  = [];', 'protected $guarded = [];')
        with open(filepath, 'w') as f:
            f.write(new_content)
        print(f'Fixed {filepath}')
