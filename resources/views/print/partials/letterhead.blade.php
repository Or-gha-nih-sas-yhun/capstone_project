{{--
  Shared document letterhead.

  Every value comes from the settings table, so the same markup serves any
  barangay. The surrounding template supplies the CSS (.top-header, .logo,
  .header-text, .republic, .province, .municipality, .barangay).

  @param bool $republic  Include the "Republic of the Philippines" line.
--}}
@php($republic = $republic ?? true)
<div class="top-header">
  <div class="logo">
    <img src="{{ setting_image('brand.logo') }}" alt="{{ barangay_label() }} Logo">
  </div>
  <div class="header-text">
    @if($republic)
      <div class="republic">Republic of the Philippines</div>
    @endif
    @if(barangay_province_line())
      <div class="province">{{ barangay_province_line() }}</div>
    @endif
    @if(barangay_municipality_line())
      <div class="municipality">{{ barangay_municipality_line() }}</div>
    @endif
    <div class="barangay">{{ strtoupper(barangay_label()) }}</div>
  </div>
  <div class="logo">
    <img src="{{ setting_image('brand.municipality_logo', 'assets/images/municipality_logo.png') }}"
         alt="{{ setting('barangay.municipality', 'Municipality') }} Logo">
  </div>
</div>
