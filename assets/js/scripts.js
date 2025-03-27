console.log("fichier chargé");
setInterval(() => {
    console.log("set interval");
    fetch('/site_web_corpus/queue/worker.php')
        .then(r => r.text())
        .then(console.log)
        .catch(console.error);
    console.log("traité");
}, 60000); // toutes les 60 secondes

