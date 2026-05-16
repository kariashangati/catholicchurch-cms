<div class="table-container">
    <table class="data-table">
        @if(!empty($headings ?? []))
            <thead>
                <tr>
                    @foreach(($headings ?? []) as $heading)
                        <th class="{{ $heading['class'] ?? '' }}">{{ $heading['label'] ?? '' }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif

        <tbody>
            @forelse(($rows ?? []) as $row)
                <tr>
                    @foreach(($row['cells'] ?? []) as $cell)
                        <td class="{{ $cell['class'] ?? '' }}">{!! $cell['value'] ?? '' !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings ?? []) ?: 1 }}" class="text-center muted">
                        {{ db_trans('no_data_available') ?: 'No data available' }}
                    </td>
                </tr>
            @endforelse
        </tbody>

        @if(!empty($footer ?? []))
            <tfoot>
                <tr>
                    @foreach($footer as $cell)
                        <td class="{{ $cell['class'] ?? '' }}">{!! $cell['value'] ?? '' !!}</td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>
</div>
