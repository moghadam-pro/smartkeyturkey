document.addEventListener('DOMContentLoaded',function(){
	var button=document.querySelector('.skt-menu-toggle');
	var nav=document.getElementById('skt-primary-nav');
	if(!button||!nav){return;}
	var close=function(){button.setAttribute('aria-expanded','false');nav.classList.remove('is-open');};
	button.addEventListener('click',function(){var open=button.getAttribute('aria-expanded')==='true';button.setAttribute('aria-expanded',open?'false':'true');nav.classList.toggle('is-open',!open);});
	nav.addEventListener('click',function(event){if(event.target.closest('a')){close();}});
	document.addEventListener('keydown',function(event){if(event.key==='Escape'){close();button.focus();}});
	window.addEventListener('resize',function(){if(window.innerWidth>780){close();}});
});
