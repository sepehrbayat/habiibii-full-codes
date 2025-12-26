// Copy and paste this into browser console on admin dashboard page
// کپی و چسباندن این کد در کنسول مرورگر در صفحه داشبورد ادمین

console.log('🔍 Starting Beauty Module Diagnostic...\n');

// Test 1: Check if element exists in HTML source
const htmlSource = document.documentElement.outerHTML;
const inSource = htmlSource.includes('data-module-type="beauty"');
console.log('1. In HTML source:', inSource ? 'YES ✓' : 'NO ✗');

// Test 2: Check if element exists in DOM
const inDOM = document.querySelectorAll('[data-module-type="beauty"]').length;
console.log('2. In DOM:', inDOM > 0 ? `YES ✓ (${inDOM} found)` : 'NO ✗');

// Test 3: Check modules section
const modulesSection = document.querySelector('.__nav-module-items');
if (modulesSection) {
    const allModules = modulesSection.querySelectorAll('a.set-module');
    console.log('3. Total modules in section:', allModules.length);
    allModules.forEach((m, i) => {
        console.log(`   [${i+1}] ID: ${m.getAttribute('data-module-id')}, Type: ${m.getAttribute('data-module-type')}`);
    });
} else {
    console.log('3. Modules section: NOT FOUND ✗');
}

// Test 4: Check if hidden by CSS
if (inDOM > 0) {
    const el = document.querySelector('[data-module-type="beauty"]');
    const style = window.getComputedStyle(el);
    console.log('4. CSS Check:');
    console.log('   display:', style.display);
    console.log('   visibility:', style.visibility);
    console.log('   opacity:', style.opacity);
    console.log('   Hidden:', style.display === 'none' ? 'YES ✗' : 'NO ✓');
}

// Test 5: Check Network Response (manual)
console.log('\n5. Network Check (MANUAL):');
console.log('   Go to Network tab → Find HTML request → Response tab');
console.log('   Search for: data-module-type="beauty"');

// Conclusion
if (inSource && inDOM === 0) {
    console.log('\n❌ ISSUE: Element in HTML but NOT in DOM');
    console.log('   → JavaScript removed it after page load');
} else if (!inSource) {
    console.log('\n❌ ISSUE: Element NOT in HTML source');
    console.log('   → Server cache or view issue');
} else {
    console.log('\n✓ Element exists in both HTML and DOM');
}

