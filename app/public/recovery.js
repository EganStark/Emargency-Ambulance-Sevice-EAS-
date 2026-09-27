function forgotPasswordPage(){
 if(user){location.hash='#security';return}
 page.innerHTML=title('Reset your password','Enter your account email to create a short-lived reset link.')+`<section class="container"><div class="form-card">${card('<form id="forgot-password-form"><label>Email<input type="email" name="email" autocomplete="email" required></label><button>Create reset link</button></form><div id="reset-result"></div><p><a href="#account">Back to sign in</a></p>')}</div></section>`;
}
function resetPasswordPage(){
 const token=new URLSearchParams(location.hash.split('?')[1]||'').get('token')||'';
 page.innerHTML=title('Choose a new password','Reset links expire after 30 minutes and can be used only once.')+`<section class="container"><div class="form-card">${token?card(`<form id="reset-password-form"><input type="hidden" name="token" value="${esc(token)}"><label>New password<input type="password" name="password" minlength="10" autocomplete="new-password" required></label><label>Confirm new password<input type="password" name="confirmPassword" minlength="10" autocomplete="new-password" required></label><button>Reset password</button></form>`):'<div class="notice warning">This reset link is missing its token.</div>'}</div></section>`;
}
function addForgotLink(){const form=document.querySelector('#login-form');if(form&&!document.querySelector('#forgot-password-link'))form.insertAdjacentHTML('afterend','<p><a id="forgot-password-link" href="#forgot-password">Forgot your password?</a></p>')}
new MutationObserver(addForgotLink).observe(page,{childList:true,subtree:true});
setTimeout(addForgotLink,0);
document.addEventListener('submit',async event=>{
 if(event.target.id==='forgot-password-form'){
  event.preventDefault();event.stopImmediatePropagation();
  try{const data=await send('/password/forgot',Object.fromEntries(new FormData(event.target)));const result=document.querySelector('#reset-result');result.innerHTML=`<div class="notice"><p>${esc(data.message)}</p>${data.resetUrl?`<p><strong>Local development link:</strong><br><a id="development-reset-link" href="${esc(new URL(data.resetUrl).hash)}">Open password reset</a></p>`:''}</div>`}catch(error){toast(error.message)}
 }
 if(event.target.id==='reset-password-form'){
  event.preventDefault();event.stopImmediatePropagation();const data=Object.fromEntries(new FormData(event.target));
  if(data.password!==data.confirmPassword)return toast('Passwords do not match');
  try{await send('/password/reset',{token:data.token,password:data.password});user=null;toast('Password reset. Sign in with your new password.');location.hash='#account';render()}catch(error){toast(error.message)}
 }
},true);
