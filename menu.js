document.querySelectorAll(".dropdown-toggle").forEach(button =>{
    button.addEventListener("click", function () {
        const dropdown = this.closest(".dropdown");
        const isOpen = dropdown.classList.contains("open");
        
        //CLose other dropdowns first
        document.querySelectorAll(".dropdown").forEach(item =>{
            item.classList.remove("open");

            const toggle = item.querySelector(".dropdown-toggle");
            if(toggle){
                toggle.setAttribute("aria-expanded", "false");
            }
        });

        //Toggle selected dropdown
        if(!isOpen){
            dropdown.classList.add("open");
            this.setAttribute("aria-expanded", "true");
        }
    });
});

//Close dropdowns when clicking outside
document.addEventListener("click", function (event){
    if(!event.target.closest(".dropdown")){
        document.querySelectorAll(".dropdown").forEach(item =>{
            item.classList.remove("open");
            const button = item.querySelector(".dropdown-toggle");
            if(button){
                button.setAttribute("aria-expanded", false);
            }
        });
    }
});