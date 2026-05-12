# QA Testing Checklist - Mobile Responsive Implementation

**Project:** Laporan BMKG/SIMPels  
**Feature:** Mobile Responsive UI  
**Date:** May 12, 2026  
**Status:** Ready for QA

---

## 🎯 Testing Overview

Aplikasi telah diperbaiki untuk responsif di semua ukuran layar. Testing mencakup:
- Desktop (> 768px)
- Tablet (481px - 768px)  
- Mobile Phone (≤ 480px)

---

## 📱 DESKTOP TESTING (> 768px)

### Layout & Navigation
- [ ] Sidebar 260px visible di kiri
- [ ] Sidebar background gradient biru correct
- [ ] Topbar blue gradient ditampilkan
- [ ] Navigation links dalam sidebar horizontal stacked
- [ ] Logo BMKG terlihat di sidebar brand

### Form Layout
- [ ] Form grid 2-3 kolom ditampilkan
- [ ] Card padding normal (22px)
- [ ] Hero section gradient visible
- [ ] Section card borders dan shadows correct

### Tabel
- [ ] Semua kolom tabel terlihat tanpa scroll
- [ ] Table header biru dengan text putih
- [ ] Table rows alternating background
- [ ] No horizontal overflow pada tabel

### Action Buttons
- [ ] "Simpan Draft" dan "Generate Laporan" sejajar horizontal
- [ ] Buttons di kanan bawah form
- [ ] Sticky positioning kerja (melihat saat scroll down)
- [ ] Button hover effects bekerja
- [ ] Buttons tidak menutupi konten

### Inputs & Files
- [ ] Input text normal width
- [ ] File input labeled "Choose File" rapi
- [ ] Autofill petugas bekerja (Nama ↔ NIP)
- [ ] Datalist dropdown muncul dengan benar

### Responsive Breakpoint Check
- [ ] Ukuran 1200px: Optimal layout
- [ ] Ukuran 1024px: Still desktop mode
- [ ] Ukuran 900px: Still desktop mode

---

## 📱 TABLET TESTING (481px - 768px)

### Layout Transformation
- [ ] Sidebar jadi horizontal di atas
- [ ] Navigation links dalam 1-2 baris horizontal
- [ ] Topbar padding reduced (14px)
- [ ] User box full-width atau right-aligned
- [ ] No vertical overlap components

### Form Responsiveness
- [ ] Grid berubah menjadi 1 kolom
- [ ] Card padding berkurang (16px)
- [ ] Hero section stack vertikal jika ada image
- [ ] Section gaps reduced (12px)
- [ ] Font sizes reduced (12px body)

### Tabel Horizontal Scroll
- [ ] Tabel punya horizontal scroll capability
- [ ] `-webkit-overflow-scrolling: touch` smooth scroll
- [ ] Header tabel tetap readable
- [ ] Petugas table scrollable lengkap
- [ ] Kegiatan table scrollable lengkap
- [ ] Checklist table scrollable lengkap
- [ ] Penggantian table scrollable lengkap
- [ ] Input dalam tabel tidak overflow
- [ ] File upload dalam tabel visible

### Action Buttons
- [ ] Buttons stack vertikal
- [ ] Buttons full-width (100%)
- [ ] Action bar FIXED di bottom (tidak sticky)
- [ ] Action bar background gradient proper
- [ ] Content tidak tertutup action bar
- [ ] Page body padding-bottom 120px working
- [ ] Gap antar buttons 8px

### Inputs & File Upload
- [ ] Input field width 100%
- [ ] Input padding 9-10px
- [ ] Input font 12px
- [ ] File input max-width: 100%
- [ ] File input tidak terpotong
- [ ] Label visible dan readable
- [ ] Placeholder text visible

### Spacing Adjustments
- [ ] Padding container 14-16px
- [ ] Margin bottom sections 8px
- [ ] Gap dalam stack/inline-group 8px
- [ ] No horizontal overflow anywhere

### Touch Interactions
- [ ] Buttons besar enough untuk tap (min 44px)
- [ ] Scroll smooth di tabel
- [ ] Horizontal scroll scrollbar visible optional

---

## 📱 MOBILE PHONE TESTING (≤ 480px)

### CRITICAL: No Horizontal Overflow
- [ ] **CRITICAL:** Body width 100vw, overflow-x hidden
- [ ] **CRITICAL:** No konten keluar ke kanan
- [ ] **CRITICAL:** Geser horizontal tidak bisa
- [ ] Sidebar width 100vw
- [ ] All form elements fit screen

### Sidebar Mobile Compact
- [ ] Sidebar horizontal compact
- [ ] Navigation 2 kolom layout
- [ ] Brand title visible
- [ ] Brand subtitle hidden
- [ ] Logout button visible
- [ ] No sidebar vertical scroll needed

### Topbar Mobile
- [ ] Topbar stack vertikal
- [ ] Title & subtitle font reduced (16px, 11px)
- [ ] User box full-width bawah
- [ ] All text readable
- [ ] No text overflow

### Form Mobile Aggressive
- [ ] Grid strict 1 kolom
- [ ] Card padding aggressive (14px)
- [ ] Hero section full-width stacked
- [ ] Font sizes reduced (10-11px)
- [ ] Section gaps 10px
- [ ] Label uppercase readable (9px)

### Tabel Mobile Mandatory Scroll
- [ ] **CRITICAL:** Semua tabel horizontal scroll mandatory
- [ ] Petugas table min-width: 650px
- [ ] Kegiatan table scrollable
- [ ] Checklist table scrollable (banyak kolom)
- [ ] Penggantian table scrollable
- [ ] Header tetap readable (8px)
- [ ] Cell padding 6-8px
- [ ] Input dalam tabel 10px, readable
- [ ] File upload dalam tabel visible

### Action Bar Fixed Bottom
- [ ] **CRITICAL:** Action bar FIXED di bottom
- [ ] Action bar tidak mengikuti scroll
- [ ] Buttons FULL-WIDTH dalam action bar
- [ ] Gap buttons 6px
- [ ] Action bar background proper #f0f4ff
- [ ] Border top visible
- [ ] Shadow proper
- [ ] Padding 10px
- [ ] Z-index 100 (above content)

### Content Visibility
- [ ] **CRITICAL:** No content hidden by action bar
- [ ] Page body padding-bottom 120px working
- [ ] Form fields semua accessible
- [ ] Last form field above action bar min 20px space
- [ ] Can scroll to see all content

### Inputs Mobile Optimized
- [ ] Input font 16px (prevent iOS zoom)
- [ ] Input padding 10px
- [ ] Input width 100%
- [ ] Input height 40px+ (tap target min 44px)
- [ ] File input 16px font
- [ ] File input label readable
- [ ] File input not broken

### Buttons Mobile
- [ ] Button + Tambah full-width
- [ ] Button Hapus proper size
- [ ] Button Simpan Draft full-width
- [ ] Button Generate Laporan full-width
- [ ] Button hover/active states kerja
- [ ] All buttons tap-friendly

### Text & Typography
- [ ] Body text 10-11px readable
- [ ] Header 16px readable
- [ ] Subtitle 11px readable
- [ ] Label 9px uppercase readable
- [ ] No text cut-off
- [ ] Line-height proper

### Specific Phone Models

#### iPhone SE (375px)
- [ ] All content fit screen
- [ ] No horizontal scroll needed
- [ ] Buttons all tappable
- [ ] Text readable
- [ ] Sidebar 2 cols nav

#### iPhone 12/13 (390px)
- [ ] Same as SE
- [ ] Extra 15px space handled properly
- [ ] Layout not breaking

#### iPhone 14 Pro Max (430px)
- [ ] Close to tablet behavior
- [ ] Still mobile responsive
- [ ] No jumping to tablet layout

#### iPad Mini Landscape (768px)
- [ ] Should be tablet layout
- [ ] Not mobile layout
- [ ] Grid 1 kolom proper

### Touch Gestures
- [ ] Horizontal table scroll smooth
- [ ] No sticky hover states blocking content
- [ ] Buttons responsive to tap
- [ ] Form inputs focus properly on tap

---

## 🔧 TECHNICAL TESTING

### CSS Classes Verification
- [ ] `table-wrap` class present on all tables
- [ ] `line-table` class applied correctly
- [ ] `actions` class on action bar
- [ ] `page-body` has padding-bottom: 120px
- [ ] `section-card` padding adjusts per breakpoint
- [ ] `grid-2`, `grid-3` responsive

### Media Query Breakpoints
- [ ] 768px breakpoint working
- [ ] 480px breakpoint working
- [ ] 640px extra handling working (if any)
- [ ] No "in-between" layout quirks

### Browser DevTools
- [ ] Chrome: Responsive Mode works
- [ ] Chrome: Device Toolbar shows correct sizes
- [ ] Firefox: Responsive Mode works
- [ ] Safari: Responsive Mode works

### Console Errors
- [ ] No JavaScript errors on mobile
- [ ] No CSS warnings
- [ ] No broken image references
- [ ] All assets loaded properly

---

## 🧪 FEATURE TESTING

### Autofill Petugas (Nama ↔ NIP)
- [ ] Desktop: Datalist dropdown kerja
- [ ] Tablet: Datalist dropdown kerja
- [ ] Mobile: Datalist dropdown accessible
- [ ] Typing triggers suggestions
- [ ] Selection fills both fields
- [ ] No infinite loop behavior

### Dynamic Table Rows
- [ ] Desktop: Add/Remove buttons kerja
- [ ] Tablet: Add/Remove buttons kerja
- [ ] Mobile: Add/Remove buttons kerja
- [ ] Row numbers update on delete
- [ ] New rows formatted properly
- [ ] Input fields accessible

### Form Submission
- [ ] Desktop: Simpan Draft button kerja
- [ ] Tablet: Simpan Draft button kerja
- [ ] Mobile: Simpan Draft button kerja
- [ ] Desktop: Generate Laporan button kerja
- [ ] Tablet: Generate Laporan button kerja
- [ ] Mobile: Generate Laporan button kerja
- [ ] Form validation works

### File Upload
- [ ] Desktop: File picker opens normally
- [ ] Tablet: File picker opens normally
- [ ] Mobile: File picker opens normally
- [ ] File selected shows in input (or label changes)
- [ ] Multiple files selectable (jika required)
- [ ] File size validation works

### Navigation
- [ ] Desktop: Sidebar nav links clickable
- [ ] Tablet: Horizontal nav links clickable
- [ ] Mobile: Compact nav links clickable
- [ ] Active link highlighting works
- [ ] Navigation between form types works

---

## 📊 ALL FORM TYPES

Test setiap form type di semua breakpoints:

### WRS NG Form
- [ ] Desktop: Optimal layout
- [ ] Tablet: Tabel scrollable
- [ ] Mobile: Fully responsive
- [ ] All sections visible
- [ ] Upload file proper

### Accelerograph Form
- [ ] Desktop: Grid 2 kolom
- [ ] Tablet: Grid 1 kolom
- [ ] Mobile: Compact layout
- [ ] Checklist table scrollable
- [ ] Dynamic tables kerja

### Seismograph Form
- [ ] Desktop: Sidebar left
- [ ] Tablet: Sidebar top
- [ ] Mobile: Sidebar horizontal
- [ ] Layout transformation smooth
- [ ] All form elements responsive

---

## 🚨 Critical Issues to Catch

### Must NOT Happen:
- ❌ Horizontal scroll on body
- ❌ Content overflow off screen right
- ❌ Action bar hiding form inputs
- ❌ Unreadable text on mobile
- ❌ Input zoom on iOS (font < 16px on mobile)
- ❌ Button not tappable (< 44px height)
- ❌ Sidebar not collapsing tablet
- ❌ Tables not scrollable mobile
- ❌ Form grid not 1 column mobile
- ❌ Any JavaScript errors console

### Known Working Behaviors:
- ✅ Sticky action bar desktop
- ✅ Fixed action bar tablet/mobile
- ✅ Horizontal table scroll mobile
- ✅ 2-column nav mobile
- ✅ Reduced padding/fonts mobile

---

## 🐛 Bug Reporting Template

```
Title: [MOBILE RESPONSIVE] Issue Description

Device: [iPhone 14 / iPad / Samsung Galaxy S21 / etc]
Screen Size: [375px / 768px / etc]
Breakpoint: [Mobile / Tablet / Desktop]
Browser: [Chrome / Safari / Firefox]

Issue:
- What broke?
- Where exactly?
- What did you expect?
- What actually happened?

Screenshot: [if possible]
Reproduction Steps:
1. 
2. 
3. 

Environment:
- OS: [iOS 16 / Android 12 / etc]
- Browser Version: [123 / 120 / etc]
```

---

## ✅ Sign-Off Checklist

When all tests pass, check:

- [ ] All breakpoints tested (480, 768, 1200px)
- [ ] All form types tested (WRS, ACC, SEIS)
- [ ] All major browsers tested
- [ ] No console errors
- [ ] No horizontal overflow
- [ ] Action bar working
- [ ] Tables scrollable
- [ ] Touch interactions smooth
- [ ] Performance acceptable
- [ ] No breaking changes from desktop

---

## 📈 Performance Checklist

- [ ] Page load time < 2s on 4G
- [ ] Mobile Lighthouse score > 80
- [ ] No layout shift (CLS < 0.1)
- [ ] CSS doesn't increase load > 10KB
- [ ] No unnecessary repaints on scroll
- [ ] Smooth scrolling in tables

---

**Status:** Ready for Testing  
**Tested by:** _______________________  
**Date:** _______________________  
**Result:** ✅ Pass / ❌ Fail
