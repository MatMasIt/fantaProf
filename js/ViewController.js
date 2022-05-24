function fadeOut(el){
    el.style.opacity=1;
    (function fade(){
        if((el.style.opacity-=.1)<0){
            el.style.display="none";
        }else{
            requestAnimationFrame(fade);
        }
    })();
}
function fadeIn(el){
    el.style.opacity=0;
    el.style.display="block";
    (function fade(){
        if((el.style.opacity+=.1)>1){
            requestAnimationFrame(fade);
        }
    })();
}
var lastViewId = null;
function setView(viewId){
    if(lastView) document.querySelector("[data-view="+lastViewId+"]").style.display = "none";
    document.querySelector("[data-view="+viewId+"]").style.display="block";
    lastView = viewId;

}
function fadeView(viewId){
    if(lastView) fadeOut(document.querySelector("[data-view="+lastViewId+"]"));
    fadeIn(document.querySelector("[data-view="+viewId+"]"));
    lastViewId = viewId;
}