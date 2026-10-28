"""Validate generated HTML links, image assets, identifiers and product data."""
from pathlib import Path
from html.parser import HTMLParser
from urllib.parse import urlsplit, unquote
import json
ROOT=Path(__file__).resolve().parent.parent
OUT=ROOT/'dist'
errors=[]
class Document(HTMLParser):
 def __init__(self,path):super().__init__();self.path=path;self.ids=set();self.links=[]
 def handle_starttag(self,tag,attrs):
  a=dict(attrs)
  if 'id' in a:
   if a['id'] in self.ids:errors.append(f'{self.path.name}: duplicate id {a["id"]}')
   self.ids.add(a['id'])
  if tag=='img' and a.get('src') and not a.get('alt'):errors.append(f'{self.path.name}: image missing alt')
  for key in ['src','href']:
   if a.get(key):self.links.append(a[key])
docs={}
for p in OUT.glob('*.html'):
 doc=Document(p);doc.feed(p.read_text());docs[p.name]=doc
 assert '<h1>' in p.read_text(),p.name
for name,doc in docs.items():
 for link in doc.links:
  u=urlsplit(link)
  if u.scheme or u.netloc:continue
  target=unquote(u.path) or name
  if not (OUT/target).exists():errors.append(f'{name}: missing {target}')
  elif u.fragment and target in docs and u.fragment not in docs[target].ids:errors.append(f'{name}: missing fragment {link}')
ps=json.loads((ROOT/'data/products.json').read_text())
assert len({p['slug'] for p in ps})==len(ps)
for p in ps:
 assert (OUT/(p['slug']+'.html')).exists()
 for image in p['gallery']:assert (OUT/'assets/images'/image).exists()
if errors:raise SystemExit('\n'.join(errors))
print(f'PASS: {len(docs)} pages, local links, images, alt text, IDs and {len(ps)} products')
