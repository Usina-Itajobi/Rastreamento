import Icons from 'react-native-vector-icons/MaterialCommunityIcons';
import { useMemo } from 'react';
import { useNavigation } from '@react-navigation/native';
import * as S from './styles';
import { useAuth } from '../../context/authContext';
import getFirstAndInitialNameAccount from '../../utils/getFirstAndInitialNameAccount';

const ProfileDrawer = () => {
  const navigation = useNavigation();
  const { selectedAccount } = useAuth();

  const initials = useMemo(() => {
    return getFirstAndInitialNameAccount(
      selectedAccount.accountName || selectedAccount.nome,
    );
  }, [selectedAccount.nome]);

  const handleNavigateToProfilesChoose = () => {
    navigation.navigate('ProfilesChoose');
  };

  return (
    <S.Container onPress={handleNavigateToProfilesChoose} activeOpacity={0.7}>
      <S.Avatar>
        <S.AvatarText>{initials.nomeInitials}</S.AvatarText>
      </S.Avatar>
      <S.TextContainer>
        <S.Username numberOfLines={1}>
          {selectedAccount.accountName || initials.nome}
        </S.Username>
        <S.UserEmail numberOfLines={1}>{selectedAccount.email}</S.UserEmail>
      </S.TextContainer>

      <S.ChevronDown>
        <Icons name="chevron-down" size={24} color="gray" />
      </S.ChevronDown>
    </S.Container>
  );
};

export default ProfileDrawer;
