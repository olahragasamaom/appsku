#!/usr/bin/env python3
import sys
from weasyprint import HTML, CSS
from pathlib import Path

try:
    html_file = Path(__file__).parent / 'PANDUAN_MIGRASI_HOSTINGER.html'
    pdf_file = Path(__file__).parent / 'PANDUAN_MIGRASI_HOSTINGER.pdf'
    
    if not html_file.exists():
        print(f"Error: File {html_file} tidak ditemukan")
        sys.exit(1)
    
    print(f"Converting {html_file.name} to PDF...")
    HTML(string=html_file.read_text(encoding='utf-8')).write_pdf(str(pdf_file))
    print(f"✅ PDF berhasil dibuat: {pdf_file.name}")
    print(f"📁 Lokasi: {pdf_file}")
    
except ImportError:
    print("Error: weasyprint tidak terinstall")
    print("Jalankan: pip install weasyprint")
    sys.exit(1)
except Exception as e:
    print(f"Error: {str(e)}")
    sys.exit(1)
