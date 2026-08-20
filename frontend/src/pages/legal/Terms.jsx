import { Link } from 'react-router-dom'
import LegalPage from './LegalPage'
import styles from './Legal.module.css'
import { EDITOR_NAME, CONTACT_EMAIL } from './legalInfo'

export default function Terms() {
  return (
    <LegalPage title="Conditions générales d'utilisation">
      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>1. Objet</h2>
        <p>
          Les présentes conditions générales régissent l'utilisation de Watchly,
          application web permettant de rechercher des films, de constituer une
          collection personnelle, de noter et de commenter des œuvres, et de partager
          des listes avec d'autres utilisateurs.
        </p>
        <p>
          La création d'un compte vaut acceptation pleine et entière des présentes
          conditions ainsi que de la{' '}
          <Link to="/confidentialite">politique de confidentialité</Link>.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>2. Accès au service</h2>
        <p>
          Watchly est accessible gratuitement. Certaines fonctionnalités — collection,
          notes, avis, création de listes — nécessitent la création d'un compte.
        </p>
        <p>
          Le service est fourni « en l'état », sans garantie de disponibilité continue.
          L'éditeur se réserve le droit d'interrompre le service pour maintenance ou
          d'y mettre fin, en prévenant les utilisateurs dans un délai raisonnable
          lorsque cela est possible.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>3. Compte utilisateur</h2>
        <p>
          Vous vous engagez à fournir une adresse e-mail valide et à choisir un nom
          d'utilisateur qui ne porte pas atteinte aux droits de tiers. Vous êtes seul
          responsable de la confidentialité de votre mot de passe et des actions menées
          depuis votre compte.
        </p>
        <p>
          Le service n'est pas destiné aux personnes de moins de 15 ans, âge du
          consentement numérique en France.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>4. Contenus publiés</h2>
        <p>
          Vous restez propriétaire des avis, listes et commentaires que vous publiez. En
          les publiant, vous autorisez leur affichage au sein de l'application.
        </p>
        <p>Sont notamment interdits :</p>
        <ul>
          <li>les propos injurieux, diffamatoires, haineux ou discriminatoires ;</li>
          <li>les contenus à caractère pornographique ou violent ;</li>
          <li>les contenus portant atteinte aux droits d'auteur d'un tiers ;</li>
          <li>le spam, la publicité et les liens malveillants ;</li>
          <li>l'usurpation d'identité d'un autre utilisateur.</li>
        </ul>
        <p>
          Tout commentaire peut être signalé par un autre utilisateur. L'éditeur se
          réserve le droit de supprimer sans préavis un contenu contraire aux présentes
          conditions et, en cas de manquement grave ou répété, de suspendre ou de
          supprimer le compte concerné.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>5. Données cinématographiques</h2>
        <p>
          Les informations sur les films proviennent de l'API TMDB. L'éditeur ne
          garantit ni leur exactitude ni leur exhaustivité et ne saurait être tenu
          responsable d'une erreur dans ces données.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>6. Données personnelles</h2>
        <p>
          Le traitement de vos données personnelles ainsi que les modalités d'exercice
          de vos droits sont décrits dans la{' '}
          <Link to="/confidentialite">politique de confidentialité</Link>. Vous pouvez à
          tout moment exporter ou supprimer vos données depuis la page{' '}
          <Link to="/settings">Paramètres</Link>.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>7. Responsabilité</h2>
        <p>
          Watchly est un projet pédagogique fourni sans finalité commerciale. La
          responsabilité de l'éditeur ne saurait être engagée en cas de perte de
          données, d'indisponibilité du service ou de dommage résultant de son
          utilisation. Il vous appartient de conserver une copie de vos données à l'aide
          de la fonction d'export.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>8. Résiliation</h2>
        <p>
          Vous pouvez supprimer votre compte à tout moment depuis la page Paramètres.
          Cette suppression entraîne l'effacement définitif de votre profil et de
          l'ensemble de vos contenus.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>9. Modification des conditions</h2>
        <p>
          Les présentes conditions peuvent être modifiées. La date de dernière mise à
          jour figure en haut de page ; toute modification substantielle sera portée à
          votre connaissance lors de votre prochaine connexion.
        </p>
      </section>

      <section className={styles.section}>
        <h2 className={styles.sectionTitle}>10. Droit applicable et contact</h2>
        <p>
          Les présentes conditions sont soumises au droit français. Pour toute question,
          contactez {EDITOR_NAME} à l'adresse{' '}
          <a href={`mailto:${CONTACT_EMAIL}`}>{CONTACT_EMAIL}</a>. Les informations
          relatives à l'éditeur figurent dans les{' '}
          <Link to="/mentions-legales">mentions légales</Link>.
        </p>
      </section>
    </LegalPage>
  )
}
