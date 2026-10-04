@extends('layouts.app')

@section('title', 'Bulletin & Announcements')

@section('content')
<div class="card">
  <div class="card-header" style="flex-wrap:wrap; gap:12px;">
    <h5><i class="fas fa-bullhorn" style="color:var(--primary);margin-right:8px;"></i>Announcements ({{ $bulletins->total() }})</h5>
    <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
      <form method="GET" action="{{ route('admin.bulletins') }}" style="display:flex; gap:8px; align-items:center;">
        <select name="category" class="form-select" style="width:140px;" onchange="this.form.submit()">
          <option value="all" {{ $category === 'all' ? 'selected' : '' }}>All Categories</option>
          <option value="Advisory" {{ $category === 'Advisory' ? 'selected' : '' }}>Advisory</option>
          <option value="Event" {{ $category === 'Event' ? 'selected' : '' }}>Event</option>
          <option value="Meeting" {{ $category === 'Meeting' ? 'selected' : '' }}>Meeting</option>
          <option value="News" {{ $category === 'News' ? 'selected' : '' }}>News</option>
        </select>
        <input type="text" name="search" class="form-control" placeholder="Search bulletins..." value="{{ $search }}" style="width:200px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
        @if($search || $category !== 'all')
          <a href="{{ route('admin.bulletins') }}" class="btn btn-outline-secondary"><i class="fas fa-sync"></i></a>
        @endif
      </form>
      <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
        <i class="fas fa-plus"></i> Post Announcement
      </button>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th style="width:80px;">Pinned</th>
          <th>Category</th>
          <th>Title</th>
          <th>Content Snippet</th>
          <th>Published Date</th>
          <th>Posted By</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @if ($bulletins->isEmpty())
          <tr>
            <td colspan="7" class="text-center" style="padding:40px;">No bulletins posted yet.</td>
          </tr>
        @else
          @foreach ($bulletins as $b)
            <tr>
              <td>
                @if($b->is_pinned)
                  <span class="badge bg-warning" title="Pinned Announcement"><i class="fas fa-thumbtack"></i> Pinned</span>
                @else
                  <span class="text-muted">--</span>
                @endif
              </td>
              <td>
                <span class="badge bg-{{ $b->category === 'Advisory' ? 'danger' : ($b->category === 'Event' ? 'primary' : ($b->category === 'Meeting' ? 'info' : 'secondary')) }}">
                  {{ $b->category }}
                </span>
              </td>
              <td style="font-weight:600; color:var(--dark);">{{ $b->title }}</td>
              <td style="max-width:260px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $b->content }}">
                {{ $b->content }}
              </td>
              <td>
                <small>{{ \Carbon\Carbon::parse($b->published_at)->format('M d, Y h:i A') }}</small>
              </td>
              <td>{{ $b->creator->username ?? 'Admin' }}</td>
              <td>
                <div style="display:flex; gap:4px;">
                  <button type="button" class="btn btn-outline-primary btn-sm" onclick="openEditModal({{ json_encode($b) }})" title="Edit Bulletin">
                    <i class="fas fa-edit"></i>
                  </button>
                  <a href="{{ route('admin.bulletins.delete', $b->id) }}" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this announcement?')" title="Delete">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
  </div>
  @if ($bulletins->hasPages())
    <div style="padding:16px 20px;border-top:1px solid #e5e7eb;display:flex;justify-content:center;">
      {{ $bulletins->appends(request()->query())->links('vendor.pagination.simple-default') }}
    </div>
  @endif
</div>

<!-- Create Bulletin Modal -->
<div class="modal" id="createModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div class="modal-content" style="background:#fff; border-radius:16px; max-width:500px; width:100%; overflow:hidden; box-shadow:var(--shadow-md);">
    <div class="modal-header" style="padding:16px 24px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:between; align-items:center;">
      <h5 style="margin:0; font-weight:600;"><i class="fas fa-bullhorn" style="color:var(--primary); margin-right:8px;"></i>Post Announcement</h5>
      <button type="button" class="btn-close" onclick="closeCreateModal()" style="background:none; border:none; cursor:pointer; font-size:18px; color:var(--gray);">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.bulletins.store') }}" enctype="multipart/form-data" id="createBulletinForm">
      @csrf
      
      <div style="padding:24px; display:flex; flex-direction:column; gap:16px;">
        <div class="form-group" style="margin:0;">
          <label class="form-label">Announcement Title *</label>
          <input type="text" name="title" class="form-control" required placeholder="e.g. Barangay General Assembly Meeting">
        </div>

        <div class="grid-2" style="margin:0;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Category *</label>
            <select name="category" class="form-select" required>
              <option value="Advisory">Advisory</option>
              <option value="Event">Event</option>
              <option value="Meeting">Meeting</option>
              <option value="News">News</option>
            </select>
          </div>
          <div class="form-group" style="margin:0; display:flex; align-items:center; height:100%; margin-top:30px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input type="checkbox" name="is_pinned" value="1">
              <strong>Pin to top</strong>
            </label>
          </div>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Announcement Body / Content *</label>
          <textarea name="content" class="form-control" rows="6" required placeholder="Write details here..."></textarea>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label" for="announcement_image">Announcement Image <small style="color:#6b7280;font-weight:400;">(optional)</small></label>
          <input id="announcement_image" type="file" name="image" class="form-control" accept=".png,.jpg,.jpeg,image/png,image/jpeg" onchange="prepareAnnouncementImage(this, 'createImageStatus', 'createPublishButton')">
          <small id="createImageStatus" style="color:#6b7280;">PNG, JPG, or JPEG only. Large photos are optimized automatically before upload.</small>
        </div>
      </div>

      <div style="padding:16px 24px; border-top:1px solid #f3f4f6; background:#f9fafb; display:flex; justify-content:flex-end; gap:8px;">
        <button type="button" class="btn btn-outline-secondary" onclick="closeCreateModal()">Cancel</button>
        <button id="createPublishButton" type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Publish</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Bulletin Modal -->
<div class="modal" id="editModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
  <div class="modal-content" style="background:#fff; border-radius:16px; max-width:500px; width:100%; overflow:hidden; box-shadow:var(--shadow-md);">
    <div class="modal-header" style="padding:16px 24px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:between; align-items:center;">
      <h5 style="margin:0; font-weight:600;"><i class="fas fa-edit" style="color:var(--primary); margin-right:8px;"></i>Update Announcement</h5>
      <button type="button" class="btn-close" onclick="closeEditModal()" style="background:none; border:none; cursor:pointer; font-size:18px; color:var(--gray);">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.bulletins.update') }}" enctype="multipart/form-data" id="editBulletinForm">
      @csrf
      <input type="hidden" name="bulletin_id" id="modal_bulletin_id">
      
      <div style="padding:24px; display:flex; flex-direction:column; gap:16px;">
        <div class="form-group" style="margin:0;">
          <label class="form-label">Announcement Title *</label>
          <input type="text" name="title" id="modal_title" class="form-control" required>
        </div>

        <div class="grid-2" style="margin:0;">
          <div class="form-group" style="margin:0;">
            <label class="form-label">Category *</label>
            <select name="category" id="modal_category" class="form-select" required>
              <option value="Advisory">Advisory</option>
              <option value="Event">Event</option>
              <option value="Meeting">Meeting</option>
              <option value="News">News</option>
            </select>
          </div>
          <div class="form-group" style="margin:0; display:flex; align-items:center; height:100%; margin-top:30px;">
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
              <input type="checkbox" name="is_pinned" id="modal_is_pinned" value="1">
              <strong>Pin to top</strong>
            </label>
          </div>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label">Announcement Body / Content *</label>
          <textarea name="content" id="modal_content" class="form-control" rows="6" required></textarea>
        </div>

        <div class="form-group" style="margin:0;">
          <label class="form-label" for="modal_image">Replace Announcement Image <small style="color:#6b7280;font-weight:400;">(optional)</small></label>
          <input id="modal_image" type="file" name="image" class="form-control" accept=".png,.jpg,.jpeg,image/png,image/jpeg" onchange="prepareAnnouncementImage(this, 'editImageStatus', 'editPublishButton')">
          <small id="editImageStatus" style="color:#6b7280;">PNG, JPG, or JPEG only. Large photos are optimized automatically. Leave empty to keep the current image.</small>
        </div>
      </div>

      <div style="padding:16px 24px; border-top:1px solid #f3f4f6; background:#f9fafb; display:flex; justify-content:flex-end; gap:8px;">
        <button type="button" class="btn btn-outline-secondary" onclick="closeEditModal()">Cancel</button>
        <button id="editPublishButton" type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

@section('styles')
<style>
  .grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }
  @media(max-width:480px){
    .grid-2 { grid-template-columns: 1fr; }
  }
</style>
@endsection

<script>
  function openCreateModal() {
    document.getElementById('createModal').style.display = 'flex';
  }
  function closeCreateModal() {
    document.getElementById('createModal').style.display = 'none';
  }

  function openEditModal(bulletin) {
    document.getElementById('modal_bulletin_id').value = bulletin.id;
    document.getElementById('modal_title').value = bulletin.title;
    document.getElementById('modal_category').value = bulletin.category;
    document.getElementById('modal_content').value = bulletin.content;
    document.getElementById('modal_is_pinned').checked = !!bulletin.is_pinned;

    document.getElementById('editModal').style.display = 'flex';
  }

  function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
  }

  async function prepareAnnouncementImage(input, statusId, buttonId) {
    const file = input.files && input.files[0];
    if (!file) return;

    const status = document.getElementById(statusId);
    const button = document.getElementById(buttonId);
    const allowedTypes = ['image/png', 'image/jpeg'];
    if (!allowedTypes.includes(file.type)) {
      input.value = '';
      status.textContent = 'Please choose a PNG, JPG, or JPEG image.';
      status.style.color = '#b91c1c';
      return;
    }

    // Small images do not need processing. Large phone photos are resized and
    // encoded as JPEG locally, reducing upload time substantially.
    if (file.size <= 1024 * 1024) {
      status.textContent = `Ready to upload (${formatFileSize(file.size)}).`;
      status.style.color = '#15803d';
      return;
    }

    button.disabled = true;
    status.textContent = 'Preparing image for faster upload…';
    status.style.color = '#475569';

    try {
      const image = await loadImage(file);
      const maxDimension = 1600;
      const scale = Math.min(1, maxDimension / Math.max(image.width, image.height));
      const canvas = document.createElement('canvas');
      canvas.width = Math.round(image.width * scale);
      canvas.height = Math.round(image.height * scale);
      canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
      const optimizedBlob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.82));
      if (!optimizedBlob) throw new Error('Image conversion failed.');

      const optimizedFile = new File([optimizedBlob], file.name.replace(/\.[^.]+$/, '.jpg'), { type: 'image/jpeg' });
      const files = new DataTransfer();
      files.items.add(optimizedFile);
      input.files = files.files;
      status.textContent = `Ready to upload (${formatFileSize(file.size)} → ${formatFileSize(optimizedFile.size)}).`;
      status.style.color = '#15803d';
    } catch (error) {
      status.textContent = 'The image will upload in its original size.';
      status.style.color = '#a16207';
    } finally {
      button.disabled = false;
    }
  }

  function loadImage(file) {
    return new Promise((resolve, reject) => {
      const image = new Image();
      const url = URL.createObjectURL(file);
      image.onload = () => { URL.revokeObjectURL(url); resolve(image); };
      image.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Unable to read image.')); };
      image.src = url;
    });
  }

  function formatFileSize(bytes) {
    return bytes < 1024 * 1024 ? `${Math.ceil(bytes / 1024)} KB` : `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  }
</script>
@endsection
