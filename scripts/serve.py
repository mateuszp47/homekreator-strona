"""Development server; rebuilds source on each HTML request."""
import argparse
import http.server
import os
from pathlib import Path
import runpy
ROOT = Path(__file__).resolve().parent.parent
os.chdir(ROOT)
runpy.run_path(str(ROOT / 'scripts/build.py'))
class Handler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=str(ROOT / 'dist'), **kwargs)
    def do_GET(self):
        if '.html' in self.path or self.path.split('?')[0].endswith('/'):
            runpy.run_path(str(ROOT / 'scripts/build.py'))
        super().do_GET()
parser = argparse.ArgumentParser()
parser.add_argument('--port', type=int, default=int(os.environ.get('PORT', 3000)))
parser.add_argument('--host', default='0.0.0.0')
args, _ = parser.parse_known_args()
port = args.port
http.server.ThreadingHTTPServer((args.host, port), Handler).serve_forever()
