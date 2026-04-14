{{-- Parameters Table Component --}}
<div class="params-table-wrapper">
    <table class="params-table">
        <thead>
            <tr>
                <th>@lang('api-docs::api-docs.name')</th>
                <th>@lang('api-docs::api-docs.type')</th>
                <th>@lang('api-docs::api-docs.required')</th>
                <th>@lang('api-docs::api-docs.description')</th>
            </tr>
        </thead>
        <tbody>
            @foreach($parameters as $param)
                <tr>
                    <td>
                        <code class="param-name">{{ $param->name }}</code>
                    </td>
                    <td>
                        <span class="param-type">{{ $param->type }}</span>
                    </td>
                    <td>
                        @if($param->required)
                            <span class="badge-required">@lang('api-docs::api-docs.required')</span>
                        @else
                            <span class="badge-optional">@lang('api-docs::api-docs.optional')</span>
                        @endif
                    </td>
                    <td class="param-description">
                        @if($param->description)
                            {{ $param->description }}
                        @endif

                        @if(!empty($param->enumValues))
                            <br><span class="param-enum">Değerler: {{ implode(', ', $param->enumValues) }}</span>
                        @endif

                        @if($param->max)
                            <span class="param-constraint">max: {{ $param->max }}</span>
                        @endif

                        @if($param->min)
                            <span class="param-constraint">min: {{ $param->min }}</span>
                        @endif

                        @if($param->foreignKey)
                            <span class="param-constraint">ref: {{ $param->foreignKey }}</span>
                        @endif

                        @if($param->nullable)
                            <span class="param-constraint">nullable</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
