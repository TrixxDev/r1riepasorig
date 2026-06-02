import collections
import re

path = r"e:\r1riepasclone\storage\logs\laravel.log"
with open(path, encoding="utf-8", errors="ignore") as f:
    text = f.read()

matches = re.findall(r"1146 Table 'r1\.([^']+)' doesn't exist", text)
counts = collections.Counter(matches)
print("Missing tables from laravel.log:")
for name, n in counts.most_common():
    print(f"  {name}: {n}x")
