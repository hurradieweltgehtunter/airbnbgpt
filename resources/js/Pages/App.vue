
<script setup>
    import { ref, computed, watch, onMounted } from 'vue';
    import BaseLayout from '@/Layouts/Base2Layout.vue';
    import { Head } from '@inertiajs/vue3';
    import { reactive } from 'vue'
    import { router } from '@inertiajs/vue3'
    import { useForm } from '@inertiajs/vue3'

    const form = useForm({
        general: 'Die Wohnung liegt im Villenviertel St. Jürgen in Lübeck. Sie liegt im ersten Stock eines renovierten, frei stehenden Herrenhauses. Die Wohnung ist mit viel Naturmaterialien und Pflanzen modern eingerichtet. Sowohl Innenstadt als auch Grünflächen und die Kanäle liegen nur wenige Gehminuten entfernt. Die Wohnung ist gemütlich, modern.',
        accommodation: '4 Zimmer, davon wird eines als Unterkunft vermietet. Küche, Bad etc. dürfen mitbenutzt werden. Es gibt eine Terrasse.',
        access: 'Den Gästen steht ein 17qm Zimmer zur Verfügung. Bis auf das Schlafzimmer können alle Bereiche mitbenutzt werden. Checkin kann dank Schlüsselsafe eigenständig erfolgen, wir sind aber sonst auch vor Ort und begrüßen Gäste persönlich.',
        additional: 'Es sollte ab 21 Uhr leise sein, da ein Kind (5 Jahre) im Haushalt wohnt. Frühstück wird nicht mit angeboten, kann aber zusammen organisiert werden. Parkmöglichkeiten sind knapp, besser mit den Öffis anreisen. ',
        about: 'Ich bin locker,jung, unkompliziert, direkt, detailverliebt. Ich möchte, dass sich meine Gäste wohlfühlen und gebe mir dazu persönlich viel Mühe. Es macht mir Spaß, neue Leute kennen zu lernen. Ich habe einen informellen und humorvollen Sprachstil. Texte von mir enthalten oft Emoji und Slang-Ausdrücke. Die Texte sind kurz und prägnant, mit einer lockeren und entspannten Schreibweise. Die Sprache ist zugänglich und leicht verständlich, mit einer freundlichen und positiven Grundstimmung.',
        exampletext: 'Hallo Pia, Danke für deine Buchung. Ich freue mich sehr, dich am 13. Okt. 2023 im Trekkershuus willkommen zu heißen. CheckIn ist 15:00 Uhr möglich. Sobald du abschätzen kannst wann du ungefähr ankommen wirst sag mir bitte Bescheid. Dann kann ich persönlich empfangen. Falls du bis dahin noch Fragen hast schreib mir gerne jederzeit. Liebe Grüße Flo. Hi Pia, Ich werde dich heute leider nicht persönlich begrüßen können. Für den Check-in heute habe ich dir deshalb hier ein kleines Video, das dich mit allem vertraut macht: https://youtu.be/VH4QrK993OI Der Code den Schlüsselsafe wie auch für das Zahlenschloss am Eingang ist 8740. Du findest das Trekkershuus hier: https://maps.app.goo.gl/tXuwu9qyFELBbeQT9 Falls du noch Fragen hast oder etwas brauchst, melde dich gerne jederzeit. Ein Hinweis noch: Solltest du mit einem Haustier anreisen, bitte ich dich, dieses nicht mit ins Bett zu nehmen. Hunde, Katzen & Co. haben dort nichts verloren. Danke. Liebe Grüße Flo',
    })

    const props = defineProps(['results'])

</script>

<style scoped>

</style>

<template>
    <Head title="Dashboard" />
    <BaseLayout>
        <form class="w-1/2 mx-auto flex flex-col space-y-12" @submit.prevent="form.post('/users')">
            <div>
                <h3 class="text-3xl font-bold dark:text-white mb-3">Allgemeine Informationen</h3>
                <p>Beschreibe hier in eigenen Worten, was deine Unterkunft besonders macht. Folgende Fragestellungen können dir dabei helfen:</p>
                <ul class="list-disc">
                    <li>Was macht deine Unterkunft einzigartig im Vergleich zu anderen in der Umgebung?</li>
                    <li>Was sind die drei Hauptgründe, warum Gäste deinen Ort lieben würden?</li>
                    <li>Gibt es besondere Attraktionen oder Sehenswürdigkeiten in der Nähe, die für Gäste von Interesse sein könnten?</li>
                    <li>Welches Gefühl oder Atmosphäre bietet Ihre Unterkunft? (z.B. gemütlich, luxuriös, rustikal, modern etc.)</li>
                </ul>

                <textarea id="general" v-model="form.general" rows="4" class="mt-5 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave a comment..."></textarea>
            </div>

            <div>
                <h3 class="text-3xl font-bold dark:text-white">Deine Unterkunft</h3>
                <p>Gehen wir nun genauer auf deine Unterkunft ein. Beschreibe dein Inserat so gut wie möglich in deinen eigenen Worten. Folgende Fragestellungen können dir dabei helfen:</p>
                <ul class="list-disc">
                    <li>Wie viele Zimmer hat die Unterkunft und welche Art von Zimmern sind sie (z.B. Schlafzimmer, Wohnzimmer, Küche)?</li>
                    <li>Welche besonderen Ausstattungsmerkmale bietet jedes Zimmer? (z.B. Meerblick, Kamin, Balkon, etc.)</li>
                    <li>Welchen Stil hat die Inneneinrichtung und gibt es ein bestimmtes Thema oder Motiv, das sich durch die Räume zieht?</li>
                </ul>

                <textarea id="accommodation" v-model="form.accommodation" rows="4" class="mt-5 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave a comment..."></textarea>

            </div>
            <div>
                <h3 class="text-3xl font-bold dark:text-white">Zugang für deine Gäste</h3>
                <p>Wechseln wir nun die Perspektive auf die Seite deiner Gäste:</p>
                <ul class="list-disc">
                    <li>Gibt es bestimmte Bereiche innerhalb der Unterkunft, die ausschließlich für den Gast sind?</li>
                    <li>Gibt es gemeinsam genutzte Bereiche oder Einrichtungen, die Gäste mit anderen teilen müssen? (z.B. ein Pool, Fitnessraum)</li>
                    <li>Gibt es Bereiche, die für Gäste nicht zugänglich sind?</li>
                    <li>Wie gelangen die Gäste in die Unterkunft? (z.B. eigenständiger Check-in mit Schlüsselbox, persönliche Begrüßung durch den Gastgeber)</li>
                </ul>

                <textarea id="access" v-model="form.access" rows="4" class="mt-5 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave a comment..."></textarea>
            </div>

            <div>
                <h3 class="text-3xl font-bold dark:text-white">Weitere Angaben (optional)</h3>
                <p>Gibt es weitere relevante Informationen über deine Unterkunft?</p>
                <ul class="list-disc">
                    <li>Gibt es Hausregeln oder Richtlinien, die Gäste während ihres Aufenthalts beachten sollten?</li>
                    <li>Bieten Sie zusätzliche Dienstleistungen oder Annehmlichkeiten an, die nicht in den anderen Abschnitten erwähnt wurden? (z.B. Frühstück, Flughafentransfer)</li>
                    <li>Gibt es Besonderheiten bezüglich der Parkmöglichkeiten oder der Verkehrsanbindung?</li>
                    <li>Haben Sie irgendwelche Empfehlungen oder Tipps für Gäste, um ihren Aufenthalt noch angenehmer zu gestalten?</li>
                </ul>

                <textarea id="additional" v-model="form.additional" rows="4" class="mt-5 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave a comment..."></textarea>
            </div>


            <div>
                <h3 class="text-3xl font-bold dark:text-white">Über dich</h3>
                <p>Wie klingst du?</p>
                <ul class="list-disc">
                    <li>Gibt es Hausregeln oder Richtlinien, die Gäste während ihres Aufenthalts beachten sollten?</li>
                    <li>Bieten Sie zusätzliche Dienstleistungen oder Annehmlichkeiten an, die nicht in den anderen Abschnitten erwähnt wurden? (z.B. Frühstück, Flughafentransfer)</li>
                    <li>Gibt es Besonderheiten bezüglich der Parkmöglichkeiten oder der Verkehrsanbindung?</li>
                    <li>Haben Sie irgendwelche Empfehlungen oder Tipps für Gäste, um ihren Aufenthalt noch angenehmer zu gestalten?</li>
                </ul>

                <textarea id="about" v-model="form.about" rows="4" class="mt-5 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave a comment..."></textarea>
            </div>

            <div>
                <h3 class="text-3xl font-bold dark:text-white">Textbeispiele</h3>
                <p></p>
                <ul class="list-disc">
                    <li>Gibt es Hausregeln oder Richtlinien, die Gäste während ihres Aufenthalts beachten sollten?</li>
                    <li>Bieten Sie zusätzliche Dienstleistungen oder Annehmlichkeiten an, die nicht in den anderen Abschnitten erwähnt wurden? (z.B. Frühstück, Flughafentransfer)</li>
                    <li>Gibt es Besonderheiten bezüglich der Parkmöglichkeiten oder der Verkehrsanbindung?</li>
                    <li>Haben Sie irgendwelche Empfehlungen oder Tipps für Gäste, um ihren Aufenthalt noch angenehmer zu gestalten?</li>
                </ul>

                <textarea id="exampletext" v-model="form.exampletext" rows="4" class="mt-5 block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave a comment..."></textarea>
            </div>

            <button type="submit" class="float-right mt-6 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
        </form>

        -> {{ props.results }}
    </BaseLayout>
</template>
