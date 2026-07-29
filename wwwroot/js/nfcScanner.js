window.nfcScanner = {
    startScanning: async function (dotNetHelper) {
        if (!('NDEFReader' in window)) {
            console.warn("Web NFC is not supported on this browser/device.");
            return false;
        }

        try {
            const ndef = new NDEFReader();
            await ndef.scan();
            
            ndef.addEventListener("reading", ({ serialNumber }) => {
                // Format UID into clean string (e.g. "123a")
                const formattedUid = serialNumber.replaceAll(":", "").toLowerCase();
                dotNetHelper.invokeMethodAsync("OnNfcCardScanned", formattedUid);
            });

            return true;
        } catch (error) {
            console.error("NFC Scan error: ", error);
            return false;
        }
    }
};