@extends('layouts.admin')

@section('page-title')
    {{ __('Employee') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ url('employee') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Employee') }}</li>
@endsection

@section('action-button')
    <div class="float-end">
        @can('edit employee')
            <a href="{{ route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                data-bs-toggle="tooltip" title="{{ __('Edit') }}"class="btn btn-sm btn-primary">
                <i class="ti ti-pencil"></i>
            </a>
        @endcan
    </div>
    <div class="text-end mb-3">
        <div class="d-flex justify-content-end drp-languages">
            <ul class="list-unstyled mb-0 m-2">
                <li class="dropdown dash-h-item drp-language">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="javascript:void(0)"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-primary"> {{ __('Joining Letter') }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{ route('joiningletter.download.pdf', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('PDF') }}</a>

                        <a href="{{ route('joininglatter.download.doc', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('DOC') }}</a>
                    </div>
                </li>
            </ul>
            <ul class="list-unstyled mb-0 m-2">
                <li class="dropdown dash-h-item drp-language">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="javascript:void(0)"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-primary"> {{ __('Experience Certificate') }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{ route('exp.download.pdf', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('PDF') }}</a>

                        <a href="{{ route('exp.download.doc', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('DOC') }}</a>
                    </div>
                </li>
            </ul>
            <ul class="list-unstyled mb-0 m-2">
                <li class="dropdown dash-h-item drp-language">
                    <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="javascript:void(0)"
                        role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="drp-text hide-mob text-primary"> {{ __('NOC') }}
                            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
                    </a>
                    <div class="dropdown-menu dash-h-dropdown">
                        <a href="{{ route('noc.download.pdf', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('PDF') }}</a>

                        <a href="{{ route('noc.download.doc', $employee->id) }}" class=" btn-icon dropdown-item"
                            data-bs-toggle="tooltip" data-bs-placement="top" target="_blanks"><i
                                class="ti ti-download ">&nbsp;</i>{{ __('DOC') }}</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="row g-4">
                {{-- Personal Detail --}}
                <div class="col-sm-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{ __('Personal Detail') }}</h5>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Employee ID') }} : </strong>
                                        <span>{{ $employeesId }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm font-style">
                                        <strong class="font-bold">{{ __('Name') }} :</strong>
                                        <span>{{ $employee->name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm font-style">
                                        <strong class="font-bold">{{ __('Email') }} :</strong>
                                        <span>{{ $employee->email }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Date of Birth') }} :</strong>
                                        <span>{{ \Auth::user()->dateFormat($employee->dob) }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Phone') }} :</strong>
                                        <span>{{ $employee->phone }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Address') }} :</strong>
                                        <span>{{ $employee->address }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Salary Type') }} :</strong>
                                        <span>{{ !empty($employee->salaryType) ? $employee->salaryType->name : '' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Basic Salary') }} :</strong>
                                        <span>{{ \Auth::user()->priceFormat($employee->salary) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Company Detail --}}
                <div class="col-sm-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{ __('Company Detail') }}</h5>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Branch') }} : </strong>
                                        <span>{{ !empty($employee->branch) ? $employee->branch->name : '' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info text-sm font-style">
                                        <strong class="font-bold">{{ __('Department') }} :</strong>
                                        <span>{{ !empty($employee->department) ? $employee->department->name : '' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Designation') }} :</strong>
                                        <span>{{ !empty($employee->designation) ? $employee->designation->name : '' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="info text-sm">
                                        <strong class="font-bold">{{ __('Date Of Joining') }} :</strong>
                                        <span>{{ \Auth::user()->dateFormat($employee->company_doj) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Document Detail --}}
                <div class="col-sm-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{ __('Document Detail') }}</h5>
                            <hr>
                            <div class="row g-3">
                                @php
                                    $empIds = array_values(array_unique(array_filter([$employee->id, $employee->employee_id])));
                                    $employeedoc = \App\Models\EmployeeDocument::whereIn('employee_id', $empIds)->get()->sortByDesc('document_value')->keyBy('document_id');
                                    $logo = \App\Models\Utility::get_file('uploads/document');
                                @endphp
                                @if (!$documents->isEmpty())
                                    @foreach ($documents as $key => $document)
                                    @php
                                        $docType   = $document->document_type ?? 'file';
                                        $docRecord = $employeedoc[$document->id] ?? null;
                                        $parsed    = $docRecord ? $docRecord->getParsedValue($docType) : ['text' => null, 'file' => null, 'files' => []];
                                        $textValue = $parsed['text'];
                                        $filesList = $parsed['files'];
                                    @endphp
                                        <div class="col-md-6 col-12 mb-3">
                                            <div class="info text-sm">
                                                <strong class="font-bold d-block mb-1">{{ $document->name }} : </strong>
                                                @if ($docType === 'text')
                                                    <span class="text-muted">{{ $textValue ?? '-' }}</span>
                                                @elseif ($docType === 'both')
                                                    @if ($textValue) <span class="d-block text-muted mb-1">{{ $textValue }}</span> @endif
                                                    @if (!empty($filesList))
                                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                                            @foreach ($filesList as $fName)
                                                                @php
                                                                    $fExt = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                                                                    $fIsImg = in_array($fExt, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                                                    $fUrl = $logo . '/' . rawurlencode($fName);
                                                                @endphp
                                                                @if ($fIsImg)
                                                                    <a href="{{ $fUrl }}" target="_blank"><img src="{{ $fUrl }}" class="img-thumbnail" style="max-height:70px; max-width:100px; object-fit:cover;"></a>
                                                                @else
                                                                    <a href="{{ $fUrl }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2"><i class="ti ti-file me-1"></i>{{ $fName }}</a>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @elseif (!$textValue)
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                @else
                                                    @if (!empty($filesList))
                                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                                            @foreach ($filesList as $fName)
                                                                @php
                                                                    $fExt = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                                                                    $fIsImg = in_array($fExt, ['jpg','jpeg','png','gif','webp','bmp','svg']);
                                                                    $fUrl = $logo . '/' . rawurlencode($fName);
                                                                @endphp
                                                                @if ($fIsImg)
                                                                    <a href="{{ $fUrl }}" target="_blank"><img src="{{ $fUrl }}" class="img-thumbnail" style="max-height:70px; max-width:100px; object-fit:cover;"></a>
                                                                @else
                                                                    <a href="{{ $fUrl }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2"><i class="ti ti-file me-1"></i>{{ $fName }}</a>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-muted">
                                        No Document Type Added.!
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bank Account Detail --}}
                <div class="col-sm-12 col-md-6">
                    <div class="card h-100 mb-0">
                        <div class="card-body employee-detail-body fulls-card">
                            <h5>{{ __('Bank Account Detail') }}</h5>
                            <hr>
                            <div class="row g-3">
                                <div class="col-md-6 col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Account Holder Name') }} : </strong>
                                        <span class="text-muted">{{ $employee->account_holder_name ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="info text-sm font-style text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Account Number') }} :</strong>
                                        <span class="text-muted">{{ $employee->account_number ?: '-' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-6 col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Bank Name') }} :</strong>
                                        <span class="text-muted">{{ $employee->bank_name ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __(\App\Models\Utility::bankCodeLabel()) }} :</strong>
                                        <span class="text-muted">{{ $employee->bank_identifier_code ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Tax Payer Id') }} :</strong>
                                        <span class="text-muted">{{ $employee->tax_payer_id ?: '-' }}</span>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="info text-sm text-break" style="word-break: break-word;">
                                        <strong class="font-bold d-block mb-1">{{ __('Branch Location') }} :</strong>
                                        <span class="text-muted">{{ $employee->branch_location ?: '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {{-- Employee HR Files / Documents Section --}}
            @if (\Auth::user()->can('Manage Employee File Document') || \Auth::user()->can('View Employee File Document') || \Auth::user()->can('Download Employee File Document') || \Auth::user()->type == 'company')
            @php
                $empShowIds = array_values(array_unique(array_filter([$employee->id, $employee->employee_id])));
                $empShowFileDocs = \App\Models\EmployeeFileDocument::whereIn('employee_id', $empShowIds)->with('uploader')->orderBy('id', 'desc')->get();
            @endphp
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card mb-0">
                        <div class="card-body employee-detail-body fulls-card">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <div>
                                    <h5 class="mb-1 fw-bold text-dark">{{ __('Employee Documents') }}</h5>
                                    <small class="text-muted">{{ __('View and download uploaded HR letters, certificates, and documents.') }}</small>
                                </div>
                            </div>
                            <hr>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('DOCUMENT ID') }}</th>
                                            <th>{{ __('DOCUMENT NAME') }}</th>
                                            <th>{{ __('DOCUMENT TYPE') }}</th>
                                            <th>{{ __('FILE TYPE') }}</th>
                                            <th>{{ __('UPLOADED DATE') }}</th>
                                            <th>{{ __('UPLOADED BY') }}</th>
                                            <th width="200px">{{ __('ACTION') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($empShowFileDocs as $sIdx => $sDoc)
                                            <tr>
                                                <td>
                                                    <a class="btn btn-outline-primary" href="{{ route('employee-file-documents.preview', $sDoc->id) }}" target="_blank">
                                                        #DOC{{ sprintf('%05d', $sDoc->id) }}
                                                    </a>
                                                </td>
                                                <td>{{ $sDoc->document_name }}</td>
                                                <td>
                                                    <span class="badge rounded-pill px-3 py-1 fw-bold" style="background-color: {{ $sDoc->badge_style['bg'] }}; color: {{ $sDoc->badge_style['color'] }}; font-size: 0.76rem;">
                                                        {{ $sDoc->document_type }}
                                                    </span>
                                                </td>
                                                <td>{{ strtoupper($sDoc->file_extension) }}</td>
                                                <td>{{ $sDoc->created_at ? $sDoc->created_at->format('d M, Y') : '-' }}</td>
                                                <td>{{ $sDoc->uploader ? $sDoc->uploader->name : __('Admin') }}</td>
                                                <td class="Action">
                                                    @if (\Auth::user()->can('View Employee File Document') || \Auth::user()->can('Manage Employee File Document') || \Auth::user()->type == 'company')
                                                    <div class="action-btn bg-info me-2">
                                                        <a href="{{ route('employee-file-documents.preview', $sDoc->id) }}" target="_blank"
                                                           class="mx-3 btn btn-sm align-items-center"
                                                           data-bs-toggle="tooltip" title="{{ __('View') }}">
                                                            <span class="text-white"><i class="ti ti-eye"></i></span>
                                                        </a>
                                                    </div>
                                                    @endif
                                                    @if (\Auth::user()->can('Download Employee File Document') || \Auth::user()->type == 'company')
                                                    <div class="action-btn bg-primary me-2">
                                                        <a href="{{ route('employee-file-documents.download', $sDoc->id) }}"
                                                           class="mx-3 btn btn-sm align-items-center"
                                                           data-bs-toggle="tooltip" title="{{ __('Download') }}">
                                                            <span class="text-white"><i class="ti ti-download"></i></span>
                                                        </a>
                                                    </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4 text-muted">
                                                    {{ __('No employee documents uploaded yet.') }}
                                                </td>
                                            </tr>
                                        @endforelse
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
@endsection
