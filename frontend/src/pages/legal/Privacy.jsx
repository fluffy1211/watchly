import { Link } from 'react-router-dom'
import LegalPage from './LegalPage'
import styles from './Legal.module.css'
import { EDITOR_NAME, CONTACT_EMAIL } from './legalInfo'

const treatments = [
  {
    purpose: 'Création et gestion du compte',
    data: 'Adresse e-mail, nom d\'utilisateur, mot de passe (haché), date de création',
    basis: 'Exécution du contrat (art. 6.1.b RGPD)',
    duration: 'Jusqu\'à la suppression du compte par l\'utilisateur',
  },
  {
    purpose: 'Profil public',
    data: 'Nom d\'utilisateur, biographie, avatar',
    basis: 'Exécution du contrat (art. 6.1.b RGPD)',
    duration: 'Jusqu\'à la suppression du compte',
  },
  {
    purpose: 'Collection de films, notes et avis',
    data: 'Films ajoutés et leur statut (à voir / vu / favori), notes de 1 à 5, avis textuels',
    basis: 'Exécution du contrat (art. 6.1.b RGPD)',
    duration: 'Jusqu\'à la suppression du contenu ou du compte',
  },
  {
    purpose: 'Listes et commentaires',
    data: 'Listes de films créées, commentaires publiés, signalements de commentaires',
    basis: 'Exécution du contrat (art. 6.1.b RGPD)',
    duration: 'Jusqu\'à la suppression du contenu ou du compte',
  },
  {
    purpose: 'Sécurité et prévention des abus',
    data: 'Jeton d\'authentification, limitation du nombre de requêtes par adresse IP',
    basis: 'Intérêt légitime (art. 6.1.f RGPD)',
    duration: 'Durée de la session pour le jeton, 24 heures pour les compteurs de requêtes',
  },
]

export default function Privacy() {
  return (
    <LegalPage title="Politique de confidentialité">
      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>1. Responsable du traitement</h2>
        <p>
          Le responsable du traitement des données collectées sur Watchly est{' '}
          {EDITOR_NAME}. Pour toute question relative à vos données ou pour exercer vos
          droits : <a href={`mailto:${CONTACT_EMAIL}`}>{CONTACT_EMAIL}</a>.
        </p>
        <p>
          Watchly ne dispose pas d'un délégué à la protection des données, sa
          désignation n'étant pas obligatoire au regard de la nature et du volume des
          traitements réalisés.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>2. Données collectées et finalités</h2>
        <p>
          Watchly ne collecte que les données que vous saisissez volontairement. Aucun
          profilage, aucune décision automatisée et aucune collecte à des fins
          publicitaires ne sont mis en œuvre.
        </p>
        <div className={styles.tableWrap}>
          <table className={styles.table}>
            <thead>
              <tr>
                <th>Finalité</th>
                <th>Données</th>
                <th>Base légale</th>
                <th>Conservation</th>
              </tr>
            </thead>
            <tbody>
              {treatments.map((t) => (
                <tr key={t.purpose}>
                  <td>{t.purpose}</td>
                  <td>{t.data}</td>
                  <td>{t.basis}</td>
                  <td>{t.duration}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>3. Cookies et traceurs</h2>
        <p>
          Watchly ne dépose aucun cookie et n'utilise aucun outil de mesure d'audience
          ni de traceur publicitaire. Votre jeton d'authentification est conservé dans
          le stockage local de votre navigateur : il est strictement nécessaire au
          fonctionnement du service et ne requiert donc pas votre consentement
          préalable. Il est effacé lors de la déconnexion.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>4. Destinataires des données</h2>
        <p>
          Vos données ne sont ni vendues, ni louées, ni transmises à des tiers à des
          fins commerciales. Seul l'éditeur y accède, et uniquement pour administrer le
          service.
        </p>
        <p>
          Watchly interroge l'API TMDB pour récupérer les informations sur les films.
          Ces requêtes ne portent que sur des identifiants ou des titres de films :
          aucune donnée vous concernant n'est transmise à TMDB.
        </p>
        <p>
          Certaines de vos données sont publiques par nature au sein de l'application :
          votre nom d'utilisateur, votre biographie, votre avatar, vos listes publiques
          et vos commentaires sont visibles par les autres utilisateurs. Votre adresse
          e-mail ne l'est jamais.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>5. Transferts hors Union européenne</h2>
        <p>
          Aucun transfert de données personnelles hors de l'Union européenne n'est
          réalisé. L'application n'est pas déployée sur un hébergeur public à ce jour ;
          cette section sera mise à jour si l'hébergement retenu impliquait un tel
          transfert.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>6. Sécurité</h2>
        <p>
          Les mots de passe sont hachés et ne sont jamais stockés en clair.
          L'authentification repose sur des jetons JWT signés par une paire de clés RSA.
          Les échanges avec l'API sont protégés par des en-têtes de sécurité et une
          limitation du nombre de requêtes destinée à prévenir les attaques par force
          brute.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>7. Vos droits</h2>
        <p>
          Conformément aux articles 15 à 21 du RGPD, vous disposez des droits d'accès,
          de rectification, d'effacement, de limitation, d'opposition et de portabilité
          sur vos données.
        </p>
        <ul>
          <li>
            <strong>Accès et portabilité</strong> — le bouton « Exporter mes données »
            de la page <Link to="/settings">Paramètres</Link> vous remet l'intégralité
            de vos données dans un fichier JSON réutilisable.
          </li>
          <li>
            <strong>Rectification</strong> — votre biographie, votre avatar et votre mot
            de passe se modifient depuis votre profil et vos paramètres.
          </li>
          <li>
            <strong>Effacement</strong> — le bouton « Supprimer mon compte » de la page
            Paramètres efface définitivement votre compte ainsi que l'ensemble de vos
            collections, notes, avis, listes et commentaires. L'opération est
            irréversible.
          </li>
          <li>
            <strong>Limitation et opposition</strong> — écrivez à{' '}
            <a href={`mailto:${CONTACT_EMAIL}`}>{CONTACT_EMAIL}</a>. Une réponse vous
            sera apportée dans un délai maximal d'un mois.
          </li>
        </ul>
        <p>
          Si vous estimez, après nous avoir contactés, que vos droits ne sont pas
          respectés, vous pouvez introduire une réclamation auprès de la{' '}
          <a href="https://www.cnil.fr" target="_blank" rel="noreferrer">CNIL</a>.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>8. Modification de la politique</h2>
        <p>
          Cette politique peut évoluer avec le service. La date de dernière mise à jour
          figure en haut de page. Toute modification substantielle vous sera signalée
          lors de votre prochaine connexion.
        </p>
      </section>
    </LegalPage>
  )
}
