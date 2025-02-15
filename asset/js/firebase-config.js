import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

const firebaseConfig = {
    apiKey: "AIzaSyDbEaszmnopnPxTeU-YPD92_iipKPHEXRs",
    authDomain: "holiday-addict.firebaseapp.com",
    projectId: "holiday-addict",
    storageBucket: "holiday-addict.firebasestorage.app",
    messagingSenderId: "274281131563",
    appId: "1:274281131563:web:1e0f027555b84dbc0d846e"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

export { auth };