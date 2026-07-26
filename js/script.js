function showDemo(type) {
            const demoContent = document.getElementById('demoContent');
            const buttons = document.querySelectorAll('.demo-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            if (type === "weather") {
                document.getElementById("weatherPopup").style.display = "block";
            }
            
            const demos = {
                weather: `
                    <h3>Real-Time Weather Forecasting</h3>
                    <p><strong>Today's Weather:</strong> Sunny, 28°C</p>
                    <p><strong>7-Day Forecast:</strong> Light rain expected on Day 3</p>
                    <p><strong>Alerts:</strong> 🟡 Moderate rainfall alert - Plan irrigation accordingly</p>
                    <p><strong>Recommendations:</strong> Good conditions for sowing. Consider postponing harvest until after Day 3 rain.</p>
                `,
                crops: `
                    <h3>AI-Powered Crop Analysis</h3>
                    <p><strong>Upload a Photo:</strong> [📸 Upload Button]</p>
                    <p><strong>Analysis Result:</strong></p>
                    <p>✅ Crop Health: Good</p>
                    <p>⚠️ Disease Detected: Early blight spotted on lower leaves</p>
                    <p><strong>Suggested Action:</strong> Apply copper-based fungicide. Remove affected leaves. Monitor closely for 7 days.</p>
                `,
                machinery: `
                    <h3>Machinery Rental Service</h3>
                    <p><strong>Available Near You:</strong></p>
                    <p>🚜 Tractor - ₹800/day - 5 km away</p>
                    <p>🌾 Harvester - ₹1500/day - 8 km away</p>
                    <p>💧 Sprayer - ₹300/day - 3 km away</p>
                    <p><strong>How to Book:</strong> Select equipment → Choose dates → Confirm booking → Equipment delivered to your farm</p>
                `,
                marketplace: `
                    <h3>Direct Farmer Marketplace</h3>
                    <p><strong>Fresh Today:</strong></p>
                    <p>🥔 Potato - ₹18/kg - From Ramesh Farm (20 km)</p>
                    <p>🍅 Tomato - ₹25/kg - From Krishna Farm (15 km)</p>
                    <p>🥬 Spinach - ₹30/kg - From Lakshmi Farm (12 km)</p>
                    <p><strong>Package Options:</strong> 5kg, 10kg, 25kg</p>
                    <p><strong>Delivery:</strong> Same day for orders before 2 PM</p>
                `
            };
        
            demoContent.innerHTML = demos[type];
        }
        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({ behavior: 'smooth' });
        }
        
const menuBtn = document.querySelector('.mobile-menu-btn');
const navLinks = document.querySelector('.nav-links');

menuBtn.addEventListener('click', () => {
    navLinks.classList.toggle('active');
});

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();

        const targetId = this.getAttribute('href');
        if (targetId === '#') return;

        const targetElement = document.querySelector(targetId);
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
        }

        navLinks.classList.remove('active');
    });
});

window.addEventListener('scroll', function() {
    const sections = document.querySelectorAll('section');
    const navLinksAll = document.querySelectorAll('.nav-links a');
    let currentSectionId = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 100;
        const sectionHeight = section.clientHeight;
        if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
            currentSectionId = '#' + section.getAttribute('id');
        }
    });
    navLinksAll.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === currentSectionId) {
            link.classList.add('active');
        }
    });
});

const style = document.createElement('style');
style.textContent = `
    .nav-links a.active {
        color: var(--light-green) !important;
        font-weight: 600;
    }
`;
document.head.appendChild(style);


const apiKey = "ca42603bf7320cf4ca0a7623b37ed4e4";
function closeWeather() {
    document.getElementById("weatherPopup").style.display = "none";
}
function getWeather() {
    const city = document.getElementById("weatherCity").value;

    if (city === "") {
        alert("City name likho");
        return;
    }
    fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`)
        .then(res => res.json())
        .then(data => {
            if (data.cod !== 200) {
                document.getElementById("weatherResult").innerHTML = "❌ City not found";
                return;
            }
            document.getElementById("weatherResult").innerHTML = `
                <p><b>${data.name}</b></p>
                <p>🌡️ ${data.main.temp} °C</p>
                <p>☁️ ${data.weather[0].description}</p>
                <p>💧 Humidity: ${data.main.humidity}%</p>
            `;
        });
}
