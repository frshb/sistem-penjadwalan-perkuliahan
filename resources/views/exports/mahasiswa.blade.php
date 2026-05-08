@if(isset($isPdf) && $isPdf)
    @include('exports.header_pdf_v3', ['title' => 'DAFTAR MAHASISWA'])
@else
    @include('exports.header', ['title' => 'DAFTAR MAHASISWA'])
@endif

<table class="data-table">
    <thead>
        <tr>
             <td colspan="5" style="padding: 20px; text-align: center; color: #666; font-style: italic;">
                 Data Mahasiswa belum tersedia.
             </td>
        </tr>
    </thead>
</table>

@if(isset($isPdf) && $isPdf)
</body>
</html>
@endif
