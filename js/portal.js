// Simple script (beginner friendly) for the role pages.
// Features: fill username, set year, basic nav highlight, quick button toasts.
(function(){
  // Get value of rh_username cookie (very simple parse)
  function getUsername(){
    const parts=document.cookie.split(';');
    for(let i=0;i<parts.length;i++){
      const p=parts[i].trim();
      if(p.indexOf('rh_username=')===0){
        return decodeURIComponent(p.substring('rh_username='.length));
      }
    }
    return 'User';
  }

  function setIf(id,value){var el=document.getElementById(id); if(el){el.textContent=value;}}

  function initUsername(){
    var u=getUsername();
    setIf('adminName',u); setIf('guestName',u); setIf('guestNameProfile',u); setIf('hkName',u); setIf('rcpName',u);
  }

  function initYear(){
    var yearSpans=document.querySelectorAll('[id$="Year"]');
    var y=new Date().getFullYear();
    yearSpans.forEach(function(s){s.textContent=y;});
  }

  function initNav(){
    var navs=document.querySelectorAll('nav.alt-nav');
    navs.forEach(function(nav){
      var links=nav.querySelectorAll('.alt-nav-link');
      links.forEach(function(link){
        link.addEventListener('click',function(){
          links.forEach(function(l){l.classList.remove('active');});
          link.classList.add('active');
        });
      });
    });
  }

  function makeToastRegion(){
    var r=document.querySelector('.alt-toast-region');
    if(!r){
      r=document.createElement('div');
      r.className='alt-toast-region';
      document.body.appendChild(r);
    }
    return r;
  }

  function showToast(msg){
    var region=makeToastRegion();
    var box=document.createElement('div');
    box.className='alt-toast';
    box.textContent=msg;
    region.appendChild(box);
    setTimeout(function(){box.classList.add('show');},10);
    setTimeout(function(){box.classList.remove('show');},2500);
    setTimeout(function(){if(box.parentNode){box.parentNode.removeChild(box);} },2800);
  }

  function initQuick(){
    document.querySelectorAll('[data-quick]').forEach(function(btn){
      btn.addEventListener('click',function(){showToast('Action: '+btn.getAttribute('data-quick'));});
    });
  }

  function init(){
    initUsername();
    initYear();
    initNav();
    initQuick();
    // Example start message
    setTimeout(function(){showToast('Page Ready');},400);
  }

  document.addEventListener('DOMContentLoaded', init);
  window.SimplePortal={toast:showToast};
})();