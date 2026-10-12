@extends('layouts.app')

@section('title', 'Manage Certificate Types')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <form method="GET" action="{{ route('admin.certificates') }}" style="display:flex;gap:8px;flex:1;max-width:400px;">
    <input type="text" name="search" class="form-control" placeholder="Search certificates..."
           value="{{ $search }}">
    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
    @if ($search)
      <a href="{{ route('admin.certificates') }}" class="btn btn-secondary">Reset</a>
    @endif
  </form>
  <button class="btn btn-primary" onclick="openModal('certModal')">
    <i class="fas fa-plus"></i> Add Certificate Type
  </button>
</div>

<div class="card">
  <div class="card-header">
    <h5><i class="fas fa-certificate" style="color:var(--primary);margin-right:8px;"></i>
      Certificate Types <span style="font-size:13px;font-weight:400;color:#6b7280;">({{ $certificates->total() }})</span></h5>
  </div>
  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Category</th>
          <th>Fee</th>
          <th>Processing Days</th>
          <th>Template</th>
          <th>Requirements</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @if ($certificates->isEmpty())
          <tr><td colspan="7" class="text-center text-muted" style="padding:40px;">No certificate types found.</td></tr>
        @else
          @foreach ($certificates as $c)
            <tr>
              <td style="font-weight:600;">
                <div>{{ $c->name }}</div>
                <small class="text-muted" style="font-weight:normal;">{{ $c->description }}</small>
              </td>
              <td><span class="badge bg-secondary">{{ $c->category }}</span></td>
              <td style="font-weight:700;color:var(--primary);">₱{{ number_format($c->fee, 2) }}</td>
              <td>{{ $c->processing_days }} day{{ $c->processing_days > 1 ? 's' : '' }}</td>
              <td><code>{{ $c->template_file }}</code></td>
              <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $c->requirements }}">{{ $c->requirements ?? '—' }}</td>
              <td>
                <div style="display:flex;gap:4px;">
                  <a href="{{ route('admin.certificates', ['edit' => $c->id, 'search' => $search]) }}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                  <a href="{{ route('admin.certificates.delete', ['id' => $c->id]) }}" class="btn btn-danger btn-sm"
                     onclick="return confirm('Archive this certificate type?')"><i class="fas fa-trash"></i></a>
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  @if ($certificates->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $certificates->appends(['search' => $search])->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>

<!-- Add / Edit Modal -->
<div class="modal-overlay {{ ($editCertificate || $errors->any()) ? 'show' : '' }}" id="certModal">
  <div class="modal-box" style="max-width:560px;">
    <div class="modal-header">
      <h5>{{ $editCertificate ? 'Edit Certificate Type' : 'Add Certificate Type' }}</h5>
      <a class="modal-close" style="text-decoration:none; color:inherit; font-size:24px;" href="{{ route('admin.certificates', ['search' => $search]) }}">&times;</a>
    </div>
    <form method="POST" action="{{ route('admin.certificates.store') }}">
      @csrf
      <div class="modal-body">
        <input type="hidden" name="certificate_id" value="{{ $editCertificate ? $editCertificate->id : 0 }}">
        <div class="form-group">
          <label class="form-label">Name *</label>
          <input type="text" name="name" class="form-control" required value="{{ old('name', $editCertificate ? $editCertificate->name : '') }}" placeholder="e.g. Barangay Clearance">
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Category *</label>
            <input type="text" name="category" class="form-control" required value="{{ old('category', $editCertificate ? $editCertificate->category : 'Clearance') }}" placeholder="e.g. Clearance, Certification">
          </div>
          <div class="form-group">
            <label class="form-label">Fee (₱) *</label>
            <input type="number" step="0.01" name="fee" class="form-control" required value="{{ old('fee', $editCertificate ? $editCertificate->fee : 0.00) }}">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Processing Days *</label>
          <input type="number" name="processing_days" class="form-control" required min="1" value="{{ old('processing_days', $editCertificate ? $editCertificate->processing_days : 1) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Requirements</label>
          <textarea name="requirements" class="form-control" rows="2" placeholder="e.g. Valid ID, Proof of residency">{{ old('requirements', $editCertificate ? $editCertificate->requirements : '') }}</textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="2" placeholder="Brief description of the document">{{ old('description', $editCertificate ? $editCertificate->description : '') }}</textarea>
        </div>

        <hr style="margin:18px 0;border:none;border-top:1px solid #e5e7eb;">

        <div style="font-weight:700;font-size:14px;margin-bottom:4px;">
          <i class="fas fa-file-pen" style="color:var(--primary);margin-right:6px;"></i>Custom Document Wording
        </div>
        <p style="color:#6b7280;font-size:12.5px;margin:0 0 12px;">
          Leave blank to use the system's built-in wording for this document. Fill it in to write your own text —
          useful for a certificate type unique to {{ barangay_label() }}.
        </p>

        <div class="form-group">
          <label class="form-label">Document Heading</label>
          <input type="text" name="header_title" class="form-control"
            value="{{ old('header_title', $editCertificate ? $editCertificate->header_title : '') }}"
            placeholder="e.g. BARANGAY CERTIFICATION">
        </div>

        <div class="form-group">
          <label class="form-label">Body Text</label>
          <textarea name="body_template" class="form-control" rows="6"
            placeholder="This is to certify that {{ '{{full_name}}' }}, {{ '{{age}}' }} years of age, a resident of {{ '{{address}}' }}, {{ '{{barangay_label}}' }}, …">{{ old('body_template', $editCertificate ? $editCertificate->body_template : '') }}</textarea>
          <small class="form-text">
            Wrap each paragraph in &lt;p&gt;…&lt;/p&gt;. You may use &lt;strong&gt;, &lt;em&gt;, &lt;u&gt; and &lt;br&gt;.
          </small>
        </div>

        <details style="margin-bottom:14px;">
          <summary style="cursor:pointer;font-size:13px;font-weight:600;color:var(--primary);">
            Available placeholders ({{ count($tokens) }})
          </summary>
          <div
            style="display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:4px 14px;margin-top:10px;font-size:12px;">
            @foreach ($tokens as $token => $description)
              <div>
                <code style="background:#f3f4f6;padding:1px 4px;border-radius:3px;">{{ '{{' . $token . '}}' }}</code>
                <span style="color:#6b7280;"> — {{ $description }}</span>
              </div>
            @endforeach
          </div>
        </details>

        <div class="form-group">
          <label class="form-label">Signatory Position</label>
          <input type="text" name="signatory_position" class="form-control"
            value="{{ old('signatory_position', $editCertificate ? $editCertificate->signatory_position : '') }}"
            placeholder="e.g. Punong Barangay">
        </div>
      </div>
      <div class="modal-footer">
        <a class="btn btn-secondary" style="text-decoration:none;" href="{{ route('admin.certificates', ['search' => $search]) }}">Cancel</a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> {{ $editCertificate ? 'Update' : 'Save' }}
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
