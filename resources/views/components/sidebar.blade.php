{{-- Sidebar Component --}}
<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        @foreach($groups as $groupKey => $group)
            <div class="sidebar-group" data-group="{{ $groupKey }}">
                <button class="sidebar-group-header" onclick="toggleGroup(this)">
                    <span class="group-name">{{ $group['name'] }}</span>
                    <span class="group-count">{{ count($group['endpoints']) }}</span>
                    <svg class="chevron" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </button>
                <ul class="sidebar-endpoints">
                    @foreach($group['endpoints'] as $index => $endpoint)
                        <li>
                            <a href="#"
                               class="sidebar-endpoint"
                               data-endpoint-id="{{ $groupKey }}-{{ $index }}"
                               data-method="{{ $endpoint->method }}"
                               data-uri="{{ $endpoint->uri }}"
                               data-group="{{ $groupKey }}"
                               data-description="{{ $endpoint->description ?? '' }}"
                               data-params="{{ !empty($endpoint->parameters) ? implode(',', array_map(fn($p) => $p->name, $endpoint->parameters)) : '' }}"
                               onclick="showEndpoint('{{ $groupKey }}-{{ $index }}', event)">
                                <span class="method-badge method-badge-sm {{ $endpoint->getMethodBadgeClass() }}">
                                    {{ $endpoint->method }}
                                </span>
                                <span class="endpoint-path" title="{{ $endpoint->uri }}">
                                    {{ $endpoint->uri }}
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </nav>
</aside>
