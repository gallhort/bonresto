<!-- Widget Comparateur Flottant -->
<div id="compareWidget" class="cw-widget" style="display:none;">
    <button class="cw-toggle" id="cwToggle" onclick="cwTogglePanel()">
        <span class="cw-badge" id="cwBadge">0</span>
        <i class="fas fa-balance-scale"></i>
        <span class="cw-label">VS</span>
    </button>
    <div class="cw-panel" id="cwPanel" style="display:none;">
        <div class="cw-header">
            <strong>Liste de comparaison</strong>
            <button onclick="cwTogglePanel()" style="background:none;border:none;font-size:18px;color:#6b7280;cursor:pointer;">&times;</button>
        </div>
        <div class="cw-list" id="cwList"></div>
        <div class="cw-actions">
            <a href="/comparateur" class="cw-compare-btn" id="cwCompareBtn">
                <i class="fas fa-balance-scale"></i> Comparer
            </a>
            <button class="cw-add-btn" onclick="cwOpenSearch()" title="Ajouter un restaurant">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div class="cw-search" id="cwSearch" style="display:none;">
            <input type="text" id="cwSearchInput" placeholder="Rechercher un restaurant..." autocomplete="off">
        </div>
    </div>
</div>
<!-- Search results dropdown rendered OUTSIDE the panel to avoid overflow:hidden clipping -->
<div class="cw-search-results" id="cwSearchResults"></div>
<style>
.cw-widget{position:fixed;bottom:24px;right:24px;z-index:9990;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
.cw-toggle{display:flex;align-items:center;gap:8px;padding:12px 20px;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;border:none;border-radius:50px;font-size:14px;font-weight:700;cursor:pointer;box-shadow:0 4px 20px rgba(124,58,237,.4);transition:all .3s;position:relative}
.cw-toggle:hover{transform:translateY(-2px);box-shadow:0 6px 28px rgba(124,58,237,.5)}
.cw-badge{position:absolute;top:-6px;right:-6px;width:22px;height:22px;background:#ef4444;color:#fff;border-radius:50%;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;border:2px solid #fff}
.cw-panel{position:absolute;bottom:56px;right:0;width:300px;background:#fff;border-radius:16px;box-shadow:0 12px 48px rgba(0,0,0,.15);animation:cwSlideUp .25s ease}
@keyframes cwSlideUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.cw-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #f3f4f6;font-size:14px}
.cw-list{max-height:240px;overflow-y:auto}
.cw-item{display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f9fafb;transition:background .15s}
.cw-item:hover{background:#f9fafb}
.cw-item-photo{width:40px;height:40px;border-radius:10px;object-fit:cover;flex-shrink:0}
.cw-item-photo-placeholder{width:40px;height:40px;border-radius:10px;background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:14px;flex-shrink:0}
.cw-item-info{flex:1;min-width:0}
.cw-item-name{font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cw-item-meta{font-size:11px;color:#6b7280}
.cw-item-remove{background:none;border:none;color:#d1d5db;cursor:pointer;font-size:16px;padding:4px;transition:color .15s}
.cw-item-remove:hover{color:#ef4444}
.cw-actions{display:flex;gap:8px;padding:12px 16px;border-top:1px solid #f3f4f6}
.cw-compare-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 16px;background:#7c3aed;color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;transition:background .2s}
.cw-compare-btn:hover{background:#6d28d9;color:#fff}
.cw-compare-btn.disabled{opacity:.4;pointer-events:none}
.cw-add-btn{width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:#f3f4f6;border:none;border-radius:10px;color:#374151;font-size:16px;cursor:pointer;transition:all .2s}
.cw-add-btn:hover{background:#e5e7eb}
.cw-search{padding:8px 16px 12px;border-top:1px solid #f3f4f6;position:relative}
.cw-search input{width:100%;padding:8px 12px;border:1px solid #e5e7eb;border-radius:8px;font-size:13px;font-family:inherit}
.cw-search input:focus{border-color:#7c3aed;outline:none}
.cw-search-results{position:fixed;width:268px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.15);max-height:200px;overflow-y:auto;display:none;z-index:9999}
.cw-sr-item{display:flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer;font-size:12px;border-bottom:1px solid #f9fafb}
.cw-sr-item:hover{background:#f3f4f6}
.cw-sr-item img{width:32px;height:32px;border-radius:6px;object-fit:cover}
.cw-empty{padding:20px 16px;text-align:center;color:#9ca3af;font-size:13px}
.cw-card-btn{position:absolute;top:8px;left:8px;width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.92);border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#7c3aed;font-size:14px;box-shadow:0 2px 8px rgba(0,0,0,.1);transition:all .2s;z-index:5;opacity:.7}
.resto-photo:hover .cw-card-btn,.cw-card-btn:hover,.cw-card-btn.active{opacity:1}
.cw-card-btn.active{background:#7c3aed!important;color:#fff!important}
.cw-card-btn:hover{transform:scale(1.1)}
@media(max-width:600px){.cw-widget{bottom:16px;right:16px}.cw-panel{width:280px;right:-8px}.cw-toggle{padding:10px 16px;font-size:13px}}
</style>
<script>
(function(){
    let cwList=JSON.parse(sessionStorage.getItem('compare_list')||'[]');
    let cwPanelOpen=false,cwSearchOpen=false,cwSearchTimeout=null;
    function cwRender(){
        const widget=document.getElementById('compareWidget');
        if(!widget)return;
        const badge=document.getElementById('cwBadge');
        const list=document.getElementById('cwList');
        const compareBtn=document.getElementById('cwCompareBtn');
        badge.textContent=cwList.length;
        widget.style.display=cwList.length>0?'block':'none';
        if(cwList.length===0){
            list.innerHTML='<div class="cw-empty"><i class="fas fa-balance-scale" style="font-size:24px;display:block;margin-bottom:8px;"></i>Ajoutez des restaurants pour comparer</div>';
        }else{
            list.innerHTML=cwList.map(function(r){
                var photo=r.photo?'/'+r.photo.replace(/^\//,''):'';
                return '<div class="cw-item">'+
                    (photo?'<img class="cw-item-photo" src="'+photo+'" alt="">':'<div class="cw-item-photo-placeholder"><i class="fas fa-store"></i></div>')+
                    '<div class="cw-item-info"><div class="cw-item-name">'+(r.nom||'')+'</div>'+
                    (r.ville?'<div class="cw-item-meta">'+r.ville+'</div>':'')+
                    '</div><button class="cw-item-remove" onclick="cwRemove('+r.id+')">&times;</button></div>';
            }).join('');
        }
        compareBtn.classList.toggle('disabled',cwList.length<2);
        document.querySelectorAll('.cw-card-btn').forEach(function(btn){
            var id=parseInt(btn.dataset.id);
            btn.classList.toggle('active',!!cwList.find(function(r){return r.id===id}));
        });
        sessionStorage.setItem('compare_list',JSON.stringify(cwList));
    }
    window.cwTogglePanel=function(){cwPanelOpen=!cwPanelOpen;document.getElementById('cwPanel').style.display=cwPanelOpen?'block':'none';if(!cwPanelOpen){document.getElementById('cwSearch').style.display='none';document.getElementById('cwSearchResults').style.display='none';cwSearchOpen=false;}};
    window.cwRemove=function(id){cwList=cwList.filter(function(r){return r.id!==id});cwRender();};
    window.cwAdd=function(resto){if(cwList.length>=3)return alert('Maximum 3 restaurants');if(cwList.find(function(r){return r.id===resto.id}))return;cwList.push(resto);cwRender();document.getElementById('compareWidget').style.display='block';if(!cwPanelOpen)cwTogglePanel();document.getElementById('cwSearch').style.display='none';var inp=document.getElementById('cwSearchInput');if(inp)inp.value='';var res=document.getElementById('cwSearchResults');if(res)res.style.display='none';cwSearchOpen=false;};
    window.cwToggleResto=function(id,nom,photo,ville){cwList.find(function(r){return r.id===id})?cwRemove(id):cwAdd({id:id,nom:nom,photo:photo||'',ville:ville||''});};
    window.cwOpenSearch=function(){cwSearchOpen=!cwSearchOpen;document.getElementById('cwSearch').style.display=cwSearchOpen?'block':'none';if(!cwSearchOpen){document.getElementById('cwSearchResults').style.display='none';}if(cwSearchOpen)setTimeout(function(){document.getElementById('cwSearchInput').focus()},100);};
    function cwPositionResults(){
        var input=document.getElementById('cwSearchInput');
        var el=document.getElementById('cwSearchResults');
        if(!input||!el)return;
        var rect=input.getBoundingClientRect();
        el.style.left=rect.left+'px';
        el.style.top=(rect.bottom+4)+'px';
        el.style.width=rect.width+'px';
    }
    document.addEventListener('DOMContentLoaded',function(){
        var input=document.getElementById('cwSearchInput');
        if(input)input.addEventListener('input',function(){
            clearTimeout(cwSearchTimeout);
            var q=this.value.trim();
            if(q.length<2){document.getElementById('cwSearchResults').style.display='none';return;}
            cwSearchTimeout=setTimeout(function(){
                fetch('/api/search/autocomplete?q='+encodeURIComponent(q)).then(function(r){return r.json()}).then(function(data){
                    var el=document.getElementById('cwSearchResults');
                    var restos=(data.restaurants||[]).concat((data.results||[]).filter(function(r){return r.type==='restaurant'}));
                    if(!restos.length){el.innerHTML='<div style="padding:12px;color:#9ca3af;font-size:12px;">Aucun resultat</div>';}
                    else{el.innerHTML=restos.slice(0,5).map(function(r){var p=r.photo?'/'+r.photo.replace(/^\//,''):'';var d=JSON.stringify({id:r.id,nom:r.nom||r.name,photo:r.photo||'',ville:r.ville||''}).replace(/'/g,"\\'");return '<div class="cw-sr-item" onclick=\'cwAdd('+d+')\'>'+(p?'<img src="'+p+'" alt="">':'')+' <div><strong>'+(r.nom||r.name)+'</strong><br><small style="color:#6b7280">'+(r.ville||'')+'</small></div></div>';}).join('');}
                    cwPositionResults();
                    el.style.display='block';
                });
            },300);
        });
        cwRender();
    });
    document.addEventListener('click',function(e){if(!e.target.closest('.cw-widget')&&!e.target.closest('.cw-card-btn')&&!e.target.closest('.cw-show-btn')&&!e.target.closest('.cw-search-results')){if(cwPanelOpen){cwPanelOpen=false;document.getElementById('cwPanel').style.display='none';document.getElementById('cwSearch').style.display='none';document.getElementById('cwSearchResults').style.display='none';cwSearchOpen=false;}}});
})();
</script>