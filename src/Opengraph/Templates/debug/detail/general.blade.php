
@if ($analysis->getMetas('general'))
<table class="meta-table table table-bordered">
    <thead>
    <tr>
        <th>名稱</th>
        <th>內容</th>
        <th>完整 Tag</th>
    </tr>
    </thead>
    <tbody>
    @foreach($analysis->getMetas('general') as $meta)
        <tr>
            <td>
                <code>{{{ $meta->name }}}</code>
            </td>
            <td>
                {{{ $meta->content }}}
            </td>
            <td>
                <code>
                    {{{ $meta->outerhtml }}}
                </code>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
@else
    <div style="height: 350px">
        沒有資訊
    </div>
@endif
