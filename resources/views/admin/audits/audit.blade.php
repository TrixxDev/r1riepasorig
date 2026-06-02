@extends('admin.layouts.app')

@section('content')

  <div class="container-fluid">
    <div class="fade-in">
      <div class="row mb-3">
        <div class="col-md-12">
          <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="cil-clipboard"></i> Notikuma detaļas</h4>
            <a href="{{ route('admin.audits') }}" class="btn btn-secondary">
              <i class="cil-arrow-left"></i> Atpakaļ uz sarakstu
            </a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12">
          <div class="card shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
              <h5 class="mb-0"><i class="cil-info"></i> Vispārīgā informācija</h5>
            </div>
            <div class="card-body bg-light">
              
              <div class="row">
                <div class="col-md-4 mb-3">
                  <div class="card border-left-primary shadow-sm h-100">
                    <div class="card-body">
                      <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        <i class="cil-calendar"></i> Datums un laiks
                      </div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ \Carbon\Carbon::parse($audit->audit_time)->format('d.m.Y H:i:s') }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <div class="card border-left-success shadow-sm h-100">
                    <div class="card-body">
                      <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        <i class="cil-user"></i> Lietotājs
                      </div>
                      <div class="h6 mb-0 font-weight-bold text-gray-800">
                        {{ $audit->user_display }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <div class="card border-left-info shadow-sm h-100">
                    <div class="card-body">
                      <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        <i class="cil-location-pin"></i> IP Adrese
                      </div>
                      <div class="h6 mb-0 font-weight-bold text-gray-800">
                        <code class="text-dark">{{ $audit->audit_ip }}</code>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-12 mb-3">
                  <div class="card border-left-warning shadow-sm">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start">
                        <div style="flex: 1;">
                          <div class="text-xs font-weight-bold text-warning text-uppercase mb-2">
                            <i class="cil-description"></i> Notikuma apraksts
                          </div>
                          <div class="text-gray-800">{{ $audit->audit_event }}</div>
                        </div>
                        <div class="ml-3">
                          <span class="badge badge-pill badge-info p-2">
                            {{ App\Models\Audit::get_facility_name($audit->audit_facility) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              @if ($audit->audit_url || $audit->audit_item || $classname)
              <div class="row">
                <div class="col-md-12 mb-3">
                  <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                      <strong><i class="cil-settings"></i> Tehniskā informācija</strong>
                    </div>
                    <div class="card-body">
                      <table class="table table-sm mb-0">
                        @if ($audit->audit_url)
                        <tr>
                          <th width="200"><i class="cil-link"></i> URL Pieprasījums:</th>
                          <td><code class="text-primary">{{ $audit->audit_url }}</code></td>
                        </tr>
                        @endif
                        @if ($audit->audit_item)
                        <tr>
                          <th><i class="cil-tag"></i> Objekta ID:</th>
                          <td><span class="badge badge-dark">{{ $audit->audit_item }}</span></td>
                        </tr>
                        @endif
                        @if ($classname)
                        <tr>
                          <th><i class="cil-code"></i> Objekta klase:</th>
                          <td><code class="text-success">{{ $classname }}</code></td>
                        </tr>
                        @endif
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              @endif
            </div>
          </div>
        </div>
      </div>

      @if ($instance && $instance !== false)
      <div class="row mt-3">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header bg-primary text-white">
              <strong>Objekta dati: 
                @if($classname && class_exists($classname))
                  @if(method_exists($instance, '_compare_get_classname'))
                    {{ $instance->_compare_get_classname().' ('.$instance_id.')' }}
                  @else
                    {{ $classname.' ('.$instance_id.')' }}
                  @endif
                @else
                  {{ 'Objekts ('.$instance_id.')' }}
                @endif
              </strong>
            </div>
            <div class="card-body p-0">
                  @php 
                    $allFields = [];
                    
                    if (method_exists($instance, 'compare')) {
                      $allFields = $instance->compare($old_instance);
                    } elseif (is_object($instance) && is_object($old_instance)) {
                      $instanceArray = (array) $instance;
                      $oldInstanceArray = (array) $old_instance;
                      
                      foreach ($instanceArray as $key => $value) {
                        $oldValue = $oldInstanceArray[$key] ?? null;
                        $allFields[$key] = [$value, $oldValue];
                      }
                    } elseif (is_object($instance)) {
                      $instanceArray = (array) $instance;
                      foreach ($instanceArray as $key => $value) {
                        $allFields[$key] = [$value, null];
                      }
                    }
                  @endphp

                    @if (!empty($allFields))
                    <div class="table-responsive">
                      <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                          <tr>
                            <th width="200">Atribūts</th>
                            <th>Vecā vērtība</th>
                            <th width="50"></th>
                            <th>Jaunā vērtība</th>
                          </tr>
                        </thead>
                        <tbody>
                        @php
                          $formatAuditFieldValue = function ($value) {
                            if ($value === false || $value === null) {
                              return '';
                            }
                            if (is_bool($value)) {
                              return $value ? 'true' : 'false';
                            }
                            if (is_object($value)) {
                              $value = json_decode(json_encode($value), true);
                            }
                            if (!is_array($value)) {
                              return (string) $value;
                            }
                            if ($value === []) {
                              return '';
                            }
                            if (isset($value[0]) && (is_array($value[0]) || is_object($value[0]))) {
                              $first = $value[0];
                              if (is_object($first)) {
                                $first = json_decode(json_encode($first), true);
                              }
                              if (is_array($first) && array_key_exists('email', $first)) {
                                return implode(', ', array_map(function ($item) {
                                  if (is_object($item)) {
                                    $item = json_decode(json_encode($item), true);
                                  }
                                  return is_array($item) ? ($item['email'] ?? json_encode($item, JSON_UNESCAPED_UNICODE)) : (string) $item;
                                }, $value));
                              }
                            }
                            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                          };

                          $isHtmlContent = function ($value) {
                            if (!is_string($value) || trim($value) === '') {
                              return false;
                            }
                            return preg_match('/<(?:html|body|div|table|p|br|span|style|thead|tbody|tr|td|th|a|img|b|strong)\b/i', $value) === 1;
                          };
                        @endphp
                        @foreach ($allFields as $attribute_name => $attribute)
                          @if ($attribute!==false)
                            @php
                              $newVal = $formatAuditFieldValue($attribute[0]);
                              $oldVal = $formatAuditFieldValue($attribute[1]);
                              $oldValDisplay = strlen($oldVal) > 100 ? substr($oldVal, 0, 100) . '...' : $oldVal;
                              $hasChange = ($newVal != $oldVal) && !($newVal === '' && $oldVal === '');
                            @endphp

                            @php
                              $oldIsJson = false;
                              $newIsJson = false;
                              $oldFormatted = $oldValDisplay;
                              $newFormatted = $newVal;
                              
                              // Проверяем является ли старое значение JSON
                              if (!empty($oldVal) && (is_string($oldVal) && (str_starts_with(trim($oldVal), '{') || str_starts_with(trim($oldVal), '[')))) {
                                $decoded = @json_decode($oldVal);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                  $oldIsJson = true;
                                  $oldFormatted = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                }
                              }
                              
                              // Проверяем является ли новое значение JSON
                              if (!empty($newVal) && (is_string($newVal) && (str_starts_with(trim($newVal), '{') || str_starts_with(trim($newVal), '[')))) {
                                $decoded = @json_decode($newVal);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                  $newIsJson = true;
                                  $newFormatted = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                }
                              }

                              $oldIsHtml = $isHtmlContent($oldVal);
                              $newIsHtml = $isHtmlContent($newVal);
                              $htmlPreview = '';
                              if ($newIsHtml && $newVal !== '') {
                                $htmlPreview = $newVal;
                              } elseif ($oldIsHtml && $oldVal !== '') {
                                $htmlPreview = $oldVal;
                              }
                              $isMessageHtmlRow = trim($attribute_name) === 'message' && $htmlPreview !== '';
                            @endphp

                            @if($isMessageHtmlRow)
                            <tr>
                              <td class="align-top">
                                <span class="badge badge-secondary">{{ trim($attribute_name) }}</span>
                              </td>
                              <td class="align-top" colspan="3">
                                <div class="audit-html-preview">{!! $htmlPreview !!}</div>
                              </td>
                            </tr>
                            @else
                            <tr class="{{ $hasChange ? 'table-warning' : '' }}">
                              <td class="align-top">
                                <span class="badge {{ $hasChange ? 'badge-primary' : 'badge-secondary' }}">
                                  @if($hasChange)<i class="cil-pencil"></i> @endif{{ trim($attribute_name) }}
                                </span>
                              </td>
                              <td class="align-top">
                                @if($oldIsHtml)
                                  <div class="audit-html-preview">{!! $oldVal !!}</div>
                                @elseif($oldIsJson)
                                  <pre class="json-tree mb-0"><code class="text-muted">{{ $oldFormatted }}</code></pre>
                                @else
                                  <code class="text-muted">{{ $oldValDisplay ?: 'false' }}</code>
                                @endif
                              </td>
                              <td class="text-center align-top">
                                @if($hasChange)
                                  <i class="cil-arrow-right text-primary"></i>
                                @else
                                  —
                                @endif
                              </td>
                              <td class="align-top">
                                @if($hasChange || ($newIsHtml && $newVal !== ''))
                                  @if($newIsHtml)
                                    <div class="audit-html-preview">{!! $newVal !!}</div>
                                  @elseif($newIsJson)
                                    <pre class="json-tree mb-0"><code class="text-success"><strong>{{ $newFormatted }}</strong></code></pre>
                                  @else
                                    <code class="text-success"><strong>{{ $newVal }}</strong></code>
                                  @endif
                                @else
                                  <span class="text-muted">—</span>
                                @endif
                              </td>
                            </tr>
                            @endif
                          @endif
                        @endforeach
                        </tbody>
                      </table>
                    </div>
                    @else
                      <div class="p-3 text-center text-muted">
                        <p class="mb-0">Nav izmaiņu</p>
                      </div>
                    @endif
            </div>
          </div>
        </div>
      </div>
      @endif

      @if(count($audit->backtrace_array) > 0)
      <div class="row mt-3">
        <div class="col-md-12">
          <div class="card shadow">
            <div class="card-header bg-dark text-white">
              <h5 class="mb-0"><i class="cil-bug"></i> Stack Trace (Backtrace)</h5>
            </div>
            <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                      <thead class="thead-dark">
                        <tr>
                          <th width="40">#</th>
                          <th width="400">Fails</th>
                          <th>Izsaukums</th>
                        </tr>
                      </thead>
                      <tbody style="font-family: monospace; font-size: 12px;">
                        @php 
                          $documentRoot = str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']); 
                          $traceNum = 0;
                        @endphp

                        @foreach ($audit->backtrace_array as $calls)
                          @php
                            $file = @str_replace('\\','/', $calls['file'] ?? '');
                            $file = str_ireplace($documentRoot, '', $file);
                            $line = isset($calls['line']) ? $calls['line'] : '';
                            $function = $calls['function'] ?? '';
                            
                            $args = '';
                            if (isset($calls['args'])){
                              $argList = [];
                              foreach ($calls['args'] as $arg){
                                if (is_object($arg)){
                                  $argList[] = get_class($arg);
                                } else {
                                  $argList[] = substr((string)$arg, 0, 50);
                                }
                              }
                              $args = implode(', ', $argList);
                            }

                            $class = $calls['class'] ?? '';
                            $type = $calls['type'] ?? '';
                            $traceNum++;
                          @endphp
                          <tr>
                            <td class="text-center align-middle">
                              <span class="badge badge-pill badge-dark">{{ $traceNum }}</span>
                            </td>
                            <td class="align-middle">
                              @if($file)
                                <i class="cil-file text-muted"></i>
                                <span class="text-primary">{{ $file }}</span>
                                @if($line)
                                  <span class="badge badge-info ml-2">line {{ $line }}</span>
                                @endif
                              @else
                                <i class="cil-code text-muted"></i>
                                <span class="text-muted font-italic">[internal function]</span>
                              @endif
                            </td>
                            <td class="align-middle">
                              <code class="text-success">{{ $class }}{{ $type }}{{ $function }}</code>
                              <small class="text-muted">({{ $args ?: '...' }})</small>
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
            </div>
          </div>
        </div>
      </div>
      @endif

    </div>
  </div>

  <style>
    .border-left-primary {
      border-left: 4px solid #4e73df !important;
    }
    .border-left-success {
      border-left: 4px solid #1cc88a !important;
    }
    .border-left-info {
      border-left: 4px solid #36b9cc !important;
    }
    .border-left-warning {
      border-left: 4px solid #f6c23e !important;
    }
    .text-xs {
      font-size: 0.7rem;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .bg-gradient-primary {
      background: linear-gradient(87deg, #5e72e4 0, #825ee4 100%) !important;
    }
    .card {
      transition: all 0.3s ease;
    }
    .card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
    }
    code {
      padding: 2px 6px;
      border-radius: 3px;
      background-color: #f8f9fa;
    }
    .json-tree {
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 4px;
      padding: 10px;
      font-family: 'Courier New', Courier, monospace;
      font-size: 12px;
      line-height: 1.6;
      overflow-x: auto;
      max-height: 300px;
      overflow-y: auto;
    }
    .json-tree code {
      background: transparent;
      padding: 0;
      white-space: pre;
    }
    .audit-html-preview {
      background: #fff;
      border: 1px solid #dee2e6;
      border-radius: 4px;
      padding: 12px;
      max-height: 600px;
      overflow: auto;
      min-width: 320px;
    }
    .audit-html-preview table {
      border-collapse: collapse;
    }
  </style>

@endsection



