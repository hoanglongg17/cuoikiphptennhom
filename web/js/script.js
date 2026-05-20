document.addEventListener("DOMContentLoaded", () => {
  const animatedSections = document.querySelectorAll(".animate");

  const revealOnScroll = () => {
    const windowHeight = window.innerHeight;
    animatedSections.forEach(section => {
      const sectionTop = section.getBoundingClientRect().top;
      if (sectionTop < windowHeight - 100) {
        section.classList.add("show");
      }
    });
  };

  window.addEventListener("scroll", revealOnScroll);
  revealOnScroll(); 
});
