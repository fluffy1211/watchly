import { Link } from 'react-router-dom'
import LegalPage from './LegalPage'
import styles from './Legal.module.css'
import { EDITOR_NAME, EDITOR_STATUS, CONTACT_EMAIL } from './legalInfo'

export default function LegalNotice() {
  return (
    <LegalPage title="Mentions légales">
      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>Éditeur du site</h2>
        <p>
          Watchly est édité par {EDITOR_NAME}, {EDITOR_STATUS}, dans le cadre du projet
          fil rouge de la formation Concepteur Développeur d'Applications (IPSSI,
          session Novembre 2025).
        </p>
        <p>Directeur de la publication : {EDITOR_NAME}.</p>
        <p>
          Contact : <a href={`mailto:${CONTACT_EMAIL}`}>{CONTACT_EMAIL}</a>
        </p>
        <div className={styles.callout}>
          <p>
            Watchly est un projet pédagogique sans finalité commerciale. L'éditeur
            étant un particulier, son adresse postale n'est pas publiée : elle est
            détenue par l'hébergeur, conformément à l'article 6-III-2 de la loi
            n° 2004-575 du 21 juin 2004 pour la confiance dans l'économie numérique.
          </p>
        </div>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>Hébergement</h2>
        <p>
          L'application n'est pas déployée sur un hébergeur public à ce jour. Elle
          s'exécute dans un environnement Docker local à des fins de développement et
          d'évaluation pédagogique. Ces mentions seront complétées avec l'identité et
          l'adresse de l'hébergeur avant toute mise en ligne.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>Propriété intellectuelle</h2>
        <p>
          Le code source, la charte graphique et les contenus rédactionnels de Watchly
          sont la propriété de l'éditeur. Toute reproduction ou représentation, totale
          ou partielle, sans autorisation écrite préalable est interdite.
        </p>
        <p>
          Les avis, notes, listes et commentaires publiés restent la propriété de leurs
          auteurs. En les publiant, l'utilisateur concède à Watchly le droit de les
          afficher au sein de l'application.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>Données cinématographiques</h2>
        <p>
          Les métadonnées des films (titres, synopsis, affiches, genres, durées, notes)
          proviennent de l'API <a href="https://www.themoviedb.org" target="_blank" rel="noreferrer">TMDB</a>.
          Watchly utilise l'API de TMDB mais n'est ni approuvé ni certifié par TMDB. Les
          affiches et visuels restent la propriété de leurs ayants droit respectifs.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>Données personnelles</h2>
        <p>
          Le traitement des données personnelles est détaillé dans la{' '}
          <Link to="/confidentialite">politique de confidentialité</Link>. Les conditions
          d'utilisation du service figurent dans les <Link to="/cgu">CGU</Link>.
        </p>
      </section>
    </LegalPage>
  )
}
