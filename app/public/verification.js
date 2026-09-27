const documentNames={driver_license:'Driver licence',vehicle_registration:'Vehicle registration'};
const documentDialog=document.createElement('dialog');
documentDialog.innerHTML='<form method="dialog"><div class="row"><h2>Verification documents</h2><button class="secondary" value="cancel" aria-label="Close">×</button></div><div id="admin-document-list"></div></form>';
document.body.append(documentDialog);
async function renderDriverDocuments(){
 const panel=document.querySelector('#dashboard-panel');if(!panel||!document.querySelector('#vehicle-form')||document.querySelector('#document-upload-card'))return;
 const {documents}=await api('/vehicle/documents');
 const wrapper=document.createElement('article');wrapper.id='document-upload-card';wrapper.className='card form-card';
 wrapper.innerHTML=`<h2>Verification documents</h2><p>Upload both documents before an administrator can approve the vehicle. Replacing a document pauses availability until a new review.</p><div class="document-list">${['driver_license','vehicle_registration'].map(kind=>{const d=documents.find(x=>x.kind===kind);return `<div class="row"><div><strong>${documentNames[kind]}</strong><small>${d?`${esc(d.original_name)} · ${Math.ceil(d.size/1024)} KB`:'Not uploaded'}</small></div>${d?`<a class="button secondary" href="/api/documents/${d.id}">Download</a>`:''}</div>`}).join('')}</div><form id="document-form" enctype="multipart/form-data"><label>Document type<select name="kind"><option value="driver_license">Driver licence</option><option value="vehicle_registration">Vehicle registration</option></select></label><label>PDF, PNG, or JPEG (maximum 5 MB)<input type="file" name="document" accept="application/pdf,image/png,image/jpeg" required></label><button>Upload document</button></form>`;
 panel.append(wrapper);
}
new MutationObserver(()=>{if(user?.role==='driver'&&tab==='vehicle')renderDriverDocuments().catch(e=>toast(e.message))}).observe(page,{childList:true,subtree:true});
document.addEventListener('submit',async event=>{
 if(event.target.id!=='document-form')return;
 event.preventDefault();event.stopImmediatePropagation();
 const form=event.target,file=form.elements.document.files[0];if(!file)return toast('Choose a document');
 try{const response=await fetch('/api/vehicle/documents',{method:'POST',body:new FormData(form)});const data=await response.json();if(!response.ok)throw Error(data.error||'Upload failed');toast('Document uploaded for review');document.querySelector('#document-upload-card')?.remove();renderDriverDocuments()}catch(error){toast(error.message)}
},true);
document.addEventListener('click',async event=>{
 const button=event.target.closest('[data-review-documents]');if(!button)return;
 event.stopImmediatePropagation();
 try{const {documents}=await api(`/admin/vehicles/${button.dataset.reviewDocuments}/documents`);document.querySelector('#admin-document-list').innerHTML=documents.length?documents.map(d=>`<div class="vehicle-option"><span><strong>${documentNames[d.kind]||esc(d.kind)}</strong><small>${esc(d.original_name)} · ${Math.ceil(d.size/1024)} KB · ${fmt(d.uploaded_at)}</small></span><a class="button secondary" href="/api/documents/${d.id}">Download</a></div>`).join(''):'<div class="empty">No verification documents uploaded.</div>';documentDialog.showModal()}catch(error){toast(error.message)}
},true);
