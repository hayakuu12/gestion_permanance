document.addEventListener("DOMContentLoaded", function(){

    // active sidebar
    const currentUrl = window.location.href;

    document.querySelectorAll(".sidebar a").forEach(link => {
        if(currentUrl.includes(link.getAttribute("href"))){
            link.style.background = "#2563eb";
            link.style.color = "white";
        }
    });

    // show selected upload file name
    document.querySelectorAll(".upload-card input").forEach(input => {
        input.addEventListener("change", function(){

            const fileName = this.files[0]?.name;
            const desc = this.closest(".upload-card").querySelector(".upload-desc");

            if(fileName && desc){
                desc.innerHTML = "✅ " + fileName;
                desc.style.color = "#16a34a";
            }

        });
    });

    // confirm dangerous actions
    document.querySelectorAll(".btn-delete").forEach(btn => {
        btn.addEventListener("click", function(e){
            if(!confirm("هل أنت متأكد من هذا الإجراء؟")){
                e.preventDefault();
            }
        });
    });

    // auto hide success
    const successBox = document.querySelector(".success");

    if(successBox){
        setTimeout(() => {
            successBox.style.opacity = "0";
            successBox.style.transform = "translateY(-10px)";
            successBox.style.transition = ".5s";

            setTimeout(() => {
                successBox.remove();
            }, 500);

        }, 3500);
    }

});