
(function() {
    const basePath = (typeof window.civentralBasePath !== 'undefined') ? window.civentralBasePath : '../';
    
    // Global loader 
    window.loadCiventralScript = function(src, callback = null) {
        const script = document.createElement('script');
        script.src = basePath + src;
        script.async = false; 
        if (callback) {
            script.onload = callback;
        }
        document.body.appendChild(script);
    };

    // LOAD MODULE
    window.loadCiventralScript('assets/js/header/app.js');
    window.loadCiventralScript('assets/js/department/app.js');
    window.loadCiventralScript('assets/js/profile/app.js');
})();
